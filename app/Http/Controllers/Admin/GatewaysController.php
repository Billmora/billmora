<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Plugin;
use App\Models\Transaction;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class GatewaysController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing gateways plugin.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:gateways.view')->only(['index']);
        $this->middleware('permission:gateways.create')->only(['create', 'store']);
        $this->middleware('permission:gateways.update')->only(['edit', 'update']);
        $this->middleware('permission:gateways.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of gateway plugins with optional search filter.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Plugin::where('type', 'gateway');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%");
            });
        }

        $gateways = $query->orderByDesc('created_at')->paginate(Billmora::getGeneral('misc_admin_pagination'));
        $gateways->appends(['search' => $search]);

        return view('admin::gateways.index', compact('gateways'));
    }

    /**
     * Show the form for creating a new gateway plugin instance.
     *
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Contracts\View\View
     */
    public function create(PluginManager $manager)
    {
        $providers = $manager->getAvailableProviders('gateway');

        return view('admin::gateways.create', compact('providers'));
    }

    /**
     * Store a newly created gateway plugin instance with configuration and permissions.
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
                'required', 'string', 'max:255',
                Rule::unique('plugins', 'name')->where(fn ($query) => $query->where('type', 'gateway')),
            ],
            'instance_provider' => 'required|string',
            'instance_active' => 'boolean',
        ]);

        $configData = $this->validatePluginConfig($request, $manager, $validated['instance_provider']);

        $plugin = Plugin::create([
            'name' => $validated['instance_name'],
            'provider' => $validated['instance_provider'],
            'type' => 'gateway',
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

        $this->recordCreate('gateway.create', $plugin->toArray());

        return redirect()->route('admin.gateways')->with('success', __('common.create_success', ['attribute' => $plugin->name]));
    }

    /**
     * Show the form for editing the specified gateway plugin instance.
     *
     * @param \App\Models\Plugin $gateway
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function edit(Plugin $gateway, PluginManager $manager)
    {
        $instance = $manager->bootInstance($gateway);

        if (!$instance) {
            return back()->with('error', __('admin/plugins.provider.files_missing', ['provider' => $gateway->provider]));
        }

        $schema = $instance->getConfigSchema();

        return view('admin::gateways.edit', compact('gateway', 'schema'));
    }

    /**
     * Update the specified gateway plugin instance with configuration and permission sync.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Plugin $gateway
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Plugin $gateway, PluginManager $manager)
    {
        $validated = $request->validate([
            'instance_name' => [
                'required', 'string', 'max:255',
                Rule::unique('plugins', 'name')
                    ->where(fn($q) => $q->where('type', 'gateway'))
                    ->ignore($gateway->id),
            ],
            'instance_active' => 'boolean',
        ]);

        $configData = $this->validatePluginConfig($request, $manager, $gateway->provider);

        if (isset($validated['instance_active']) && $validated['instance_active'] == false && $gateway->is_active == true) {
            
            $isUsed = Invoice::where('plugin_id', $gateway->id)->exists() || 
                  Transaction::where('plugin_id', $gateway->id)->exists();

            if ($isUsed) {
                return redirect()->back()->with('error', __('admin/gateways.disable.in_use'));
            }
        }

        $oldGateway = $gateway->getOriginal();

        $gateway->update([
            'name' => $validated['instance_name'],
            'is_active' => (bool) $validated['instance_active'],
            'config' => $configData,
        ]);

        if ($gateway->is_active !== (bool) $oldGateway['is_active']) {
            $instance = $manager->bootInstance($gateway);

            if ($instance && method_exists($instance, 'getPermissions')) {
                $permissions = $instance->getPermissions();

                if ($gateway->is_active) {
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

        $this->recordUpdate('gateway.update', $oldGateway, $gateway->getChanges());

        return redirect()->route('admin.gateways')->with('success', __('common.update_success', ['attribute' => $gateway->name]));
    }

    /**
     * Remove the specified gateway plugin instance from storage.
     *
     * @param \App\Models\Plugin $gateway
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Plugin $gateway, PluginManager $manager)
    {
        $isUsed = Invoice::where('plugin_id', $gateway->id)->exists() || 
                  Transaction::where('plugin_id', $gateway->id)->exists();

        if ($isUsed) {
            return redirect()->route('admin.gateways')->with('error', __('admin/gateways.delete.in_use'));
        }

        $instance = $manager->bootInstance($gateway);
        if ($instance && method_exists($instance, 'getPermissions')) {
            $permissions = $instance->getPermissions();
            if (!empty($permissions)) {
                Permission::whereIn('name', $permissions)->delete();
            }
        }

        $gateway->delete();

        $this->recordDelete('gateway.delete', $gateway->toArray());

        return redirect()->route('admin.gateways')->with('success', __('common.delete_success', ['attribute' => $gateway->name]));
    }

    /**
     * Validate gateway plugin configuration based on provider schema.
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
        $instance = $manager->bootInstance(new Plugin(['provider' => $provider, 'type' => 'gateway']));
        if (!$instance) return [];

        $schema = collect($instance->getConfigSchema());
        $prefix = "configurations.{$provider}";

        $rules = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => $f['rules'] ?? 'nullable'])->all();
        $attrs = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => strtolower($f['label'] ?? $k)])->all();

        $request->validate($rules, [], $attrs);
        return $request->input($prefix, []);
    }
}
