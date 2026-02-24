<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class ModulesControler extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing modules plugin.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:modules.view')->only(['index']);
        $this->middleware('permission:modules.create')->only(['create', 'store']);
        $this->middleware('permission:modules.update')->only(['edit', 'update', 'testConnection']);
        $this->middleware('permission:modules.delete')->only(['destroy']);
    }

    /**
     * Display a listing of module plugins.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Plugin::where('type', 'module');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%");
            });
        }

        $modules = $query->orderByDesc('created_at')->paginate(25);

        $modules->appends(['search' => $search]);

        return view('admin::modules.index', compact('modules'));
    }

    /**
     * Show the form for creating a new module instance.
     *
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Contracts\View\View
     */
    public function create(PluginManager $manager)
    {
        $providers = $manager->getAvailableProviders('module');

        return view('admin::modules.create', compact('providers'));
    }

    /**
     * Store a newly created module instance in storage.
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
                Rule::unique('plugins', 'name')
                    ->where(fn($q) => $q->where('type', 'module')),
            ],
            'instance_provider' => [
                'required',
                'string',
                Rule::unique('plugins', 'provider')
                    ->where(fn($q) => $q->where('type', 'module')),
            ],
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
            'type' => 'module',
            'is_active' => (bool) $validated['instance_active'],
            'config' => $configData,
        ]);

        if ($plugin->is_active) {
            $instance = $manager->bootInstance($plugin);
            if ($instance && method_exists($instance, 'getPermissions')) {
                foreach ($instance->getPermissions() as $permissionName) {
                    Permission::firstOrCreate(['name' => $permissionName]);
                }
            }
        }

        $this->recordCreate('module.create', $plugin->toArray());

        return redirect()->route('admin.modules')->with('success', __('common.create_success', ['attribute' => $plugin->name]));
    }

    /**
     * Show the form for editing the specified modules instance.
     *
     * @param \App\Models\Plugin $module
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function edit(Plugin $module, PluginManager $manager)
    {
        $instance = $manager->bootInstance($module);

        if (!$instance) {
            return back()->with('error', "Provider files for {$module->provider} not found.");
        }

        $schema = $instance->getConfigSchema();

        return view('admin::modules.edit', compact('module', 'schema'));
    }

    /**
     * Update the specified module instance in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Plugin $module
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Plugin $module, PluginManager $manager)
    {
        $validated = $request->validate([
            'instance_name' => [
                'required', 'string', 'max:255',
                Rule::unique('plugins', 'name')
                    ->where(fn($q) => $q->where('type', 'module'))
                    ->ignore($module->id),
            ],
            'instance_active' => 'boolean',
        ]);

        $configData = $this->validatePluginConfig(
            $request, 
            $manager, 
            $module->provider
        );

        $oldModule = $module->getOriginal();

        $module->update([
            'name' => $validated['instance_name'],
            'is_active' => (bool) $validated['instance_active'],
            'config' => $configData,
        ]);

        if ($module->is_active !== (bool) $oldModule['is_active']) {
            $instance = $manager->bootInstance($module);

            if ($instance && method_exists($instance, 'getPermissions')) {
                $permissions = $instance->getPermissions();

                if ($module->is_active) {
                    foreach ($permissions as $permissionName) {
                        Permission::firstOrCreate(['name' => $permissionName]);
                    }
                } else {
                    foreach ($permissions as $permissionName) {
                        Permission::where('name', $permissionName)->delete();
                    }
                }
            }
        }

        $this->recordUpdate('module.update', $oldModule, $module->getChanges());

        return redirect()->route('admin.modules')->with('success', __('common.update_success', ['attribute' => $module->name]));
    }

    /**
     * Remove the specified module instance from storage.
     *
     * @param \App\Models\Plugin $module
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Plugin $module)
    {
        $module->delete();

        $this->recordDelete('module.delete', $module->toArray());

        return redirect()->route('admin.modules')->with('success', __('common.delete_success', ['attribute' => $module->name]));
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
        $instance = $manager->bootInstance(new Plugin(['provider' => $provider, 'type' => 'module']));
        if (!$instance) return [];

        $schema = collect($instance->getConfigSchema());
        $prefix = "configurations.{$provider}";

        $rules = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => $f['rules'] ?? 'nullable'])->all();
        $attrs = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => strtolower($f['label'] ?? $k)])->all();

        $request->validate($rules, [], $attrs);

        return $request->input($prefix, []);
    }
}
