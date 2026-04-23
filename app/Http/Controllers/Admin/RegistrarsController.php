<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Models\Registrant;
use App\Models\Tld;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrarsController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing registrars plugin.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:registrars.view')->only(['index']);
        $this->middleware('permission:registrars.create')->only(['create', 'store']);
        $this->middleware('permission:registrars.update')->only(['edit', 'update', 'testConnection']);
        $this->middleware('permission:registrars.delete')->only(['destroy']);
    }

    /**
     * Display a listing of registrar plugins.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Plugin::where('type', 'registrar');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%");
            });
        }

        $registrars = $query->orderByDesc('created_at')->paginate(Billmora::getGeneral('misc_admin_pagination'));

        $registrars->appends(['search' => $search]);

        return view('admin::registrars.index', compact('registrars'));
    }

    /**
     * Show the form for creating a new registrar instance.
     *
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Contracts\View\View
     */
    public function create(PluginManager $manager)
    {
        $providers = $manager->getAvailableProviders('registrar');

        return view('admin::registrars.create', compact('providers'));
    }

    /**
     * Store a newly created registrar instance in storage.
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
                    return $query->where('type', 'registrar');
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
            'type' => 'registrar',
            'is_active' => (bool) $validated['instance_active'],
            'config' => $configData,
        ]);

        $this->recordCreate('registrar.create', $plugin->toArray());

        return redirect()->route('admin.registrars')->with('success', __('common.create_success', ['attribute' => $plugin->name]));
    }

    /**
     * Show the form for editing the specified registrar instance.
     *
     * @param \App\Models\Plugin $registrar
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function edit(Plugin $registrar, PluginManager $manager)
    {
        $instance = $manager->bootInstance($registrar);

        if (!$instance) {
            return back()->with('error', __('admin/plugins.provider.files_missing', ['provider' => $registrar->provider]));
        }

        $schema = $instance->getConfigSchema();

        return view('admin::registrars.edit', compact('registrar', 'schema'));
    }

    /**
     * Update the specified registrar instance in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Plugin $registrar
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Plugin $registrar, PluginManager $manager)
    {
        $validated = $request->validate([
            'instance_name' => [
                'required', 'string', 'max:255',
                Rule::unique('plugins', 'name')
                    ->where(fn($q) => $q->where('type', 'registrar'))
                    ->ignore($registrar->id),
            ],
            'instance_active' => 'boolean',
        ]);

        $configData = $this->validatePluginConfig(
            $request, 
            $manager, 
            $registrar->provider
        );

        $oldRegistrar = $registrar->getOriginal();

        $registrar->update([
            'name' => $validated['instance_name'],
            'is_active' => (bool) $validated['instance_active'],
            'config' => $configData,
        ]);

        $this->recordUpdate('registrar.update', $oldRegistrar, $registrar->getChanges());

        return redirect()->route('admin.registrars')->with('success', __('common.update_success', ['attribute' => $registrar->name]));
    }

    /**
     * Remove the specified registrar instance from storage.
     *
     * @param \App\Models\Plugin $registrar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Plugin $registrar)
    {
        $isUsed = Tld::where('plugin_id', $registrar->id)->exists() || 
                  Registrant::where('plugin_id', $registrar->id)->exists();

        if ($isUsed) {
            return redirect()->route('admin.registrars')->with('error', __('admin/registrars.delete.in_use'));
        }

        $registrar->delete();

        $this->recordDelete('registrar.delete', $registrar->toArray());

        return redirect()->route('admin.registrars')->with('success', __('common.delete_success', ['attribute' => $registrar->name]));
    }

    /**
     * Test connection to the registrar provider with stored configuration.
     *
     * @param \App\Models\Plugin $registrar
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function testConnection(Plugin $registrar, PluginManager $manager)
    {
        $instance = $manager->bootInstance($registrar);

        if (!$instance) {
            return back()->with('error', __('admin/plugins.provider.files_missing', ['provider' => $registrar->provider]));
        }

        try {
            $instance->testConnection($registrar->config);

            return back()->with('success', __('admin/registrars.connection.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrars.connection.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Validate plugin configuration based on schema.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\PluginManager $manager
     * @param string $provider
     * @return array<string, mixed>
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validatePluginConfig(Request $request, PluginManager $manager, string $provider): array
    {
        $instance = $manager->bootInstance(new Plugin(['provider' => $provider, 'type' => 'registrar']));
        if (!$instance) return [];

        $schema = collect($instance->getConfigSchema());
        $prefix = "configurations.{$provider}";

        $rules = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => $f['rules'] ?? 'nullable'])->all();
        $attrs = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => strtolower($f['label'] ?? $k)])->all();

        $request->validate($rules, [], $attrs);

        return $request->input($prefix, []);
    }
}
