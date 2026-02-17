<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Services\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProvisioningsController extends Controller
{
    /**
     * Display a listing of provisioning plugins.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Plugin::where('type', 'provisioning');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%");
            });
        }

        $provisionings = $query->orderByDesc('created_at')->paginate(25);

        $provisionings->appends(['search' => $search]);

        return view('admin::provisionings.index', compact('provisionings'));
    }

    /**
     * Show the form for creating a new provisioning instance.
     *
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Contracts\View\View
     */
    public function create(PluginManager $manager)
    {
        $providers = $manager->getAvailableProviders('provisioning');

        return view('admin::provisionings.create', compact('providers'));
    }

    /**
     * Store a newly created provisioning instance in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, PluginManager $manager)
    {
        $validated = $request->validate([
            'instance_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('plugins', 'name')->where(function ($query) {
                    return $query->where('type', 'provisioning');
                }),
            ],
            'instance_provider' => 'required|string',
            'instance_active' => 'boolean',
        ]);

        $configData = $this->validatePluginConfig(
            $request, 
            $manager, 
            $validated['instance_provider']
        );

        $plugin = Plugin::create([
            'name' => $validated['instance_name'],
            'provider' => $validated['instance_provider'],
            'type' => 'provisioning',
            'is_active' => (bool) $validated['instance_active'],
            'config' => $configData,
        ]);

        return redirect()->route('admin.provisionings')->with('success', __('common.create_success', ['attribute' => $plugin->name]));
    }

    /**
     * Show the form for editing the specified provisioning instance.
     *
     * @param \App\Models\Plugin $provisioning
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function edit(Plugin $provisioning, PluginManager $manager)
    {
        $instance = $manager->bootInstance($provisioning);

        if (!$instance) {
            return back()->with('error', "Provider files for {$provisioning->provider} not found.");
        }

        $schema = $instance->getConfigSchema();

        return view('admin::provisionings.edit', compact('provisioning', 'schema'));
    }

    /**
     * Update the specified provisioning instance in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Plugin $provisioning
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Plugin $provisioning, PluginManager $manager)
    {
        $validated = $request->validate([
            'instance_name' => [
                'required', 'string', 'max:255',
                Rule::unique('plugins', 'name')
                    ->where(fn($q) => $q->where('type', 'provisioning'))
                    ->ignore($provisioning->id),
            ],
            'instance_active' => 'boolean',
        ]);

        $configData = $this->validatePluginConfig(
            $request, 
            $manager, 
            $provisioning->provider,
            $provisioning
        );

        $provisioning->update([
            'name' => $validated['instance_name'],
            'is_active' => (bool) $validated['instance_active'],
            'config' => $configData,
        ]);

        return redirect()->route('admin.provisionings')->with('success', __('common.update_success', ['attribute' => $provisioning->name]));
    }

    /**
     * Remove the specified provisioning instance from storage.
     *
     * @param \App\Models\Plugin $provisioning
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Plugin $provisioning)
    {
        $provisioning->delete();

        return redirect()->route('admin.provisionings')->with('success', __('common.delete_success', ['attribute' => $provisioning->name]));
    }

    /**
     * Test connection to the provisioning provider with stored configuration.
     *
     * @param \App\Models\Plugin $provisioning
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function testConnection(Plugin $provisioning, PluginManager $manager)
    {
        $instance = $manager->bootInstance($provisioning);

        if (!$instance) {
            return back()->with('error', "Provider files for {$provisioning->provider} not found.");
        }

        try {
            $instance->testConnection($provisioning->config);

            return back()->with('success', __('admin/provisionings.connection.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/provisionings.connection.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Validate plugin configuration based on schema with file upload handling.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\PluginManager $manager
     * @param string $provider
     * @param \App\Models\Plugin|null $currentInstance
     * @return array<string, mixed>
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validatePluginConfig(Request $request, PluginManager $manager, string $provider, ?Plugin $currentInstance = null): array
    {
        $instance = $manager->bootInstance(new Plugin(['provider' => $provider, 'type' => 'provisioning']));
        if (!$instance) return [];

        $schema = collect($instance->getConfigSchema());
        $prefix = "configurations.{$provider}";

        $rules = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => $f['rules'] ?? 'nullable'])->all();
        $attrs = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => strtolower($f['label'] ?? $k)])->all();

        $request->validate($rules, [], $attrs);

        $config = $request->input($prefix, []);

        foreach ($schema->where('type', 'file') as $key => $field) {
            $inputKey = "{$prefix}.{$key}";

            if ($request->hasFile($inputKey)) {
                $path = $request->file($inputKey)->store("plugins/{$provider}", 'public');
                $config[$key] = $path;
            } elseif ($currentInstance && isset($currentInstance->config[$key])) {
                $config[$key] = $currentInstance->config[$key];
            }
        }

        return $config;
    }
}
