<?php

namespace App\Http\Controllers\Admin\Packages;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Plugin;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;

class ProvisioningController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing packages provisioning.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:packages.update')->only(['index', 'update']);
    }

    /**
     * Display the provisioning configuration page for the specified package.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request, $id, PluginManager $manager)
    {
        $package = Package::findOrFail($id);

        $provisionings = Plugin::where('type', 'provisioning')
            ->orderBy('name')
            ->get();

        $selectedId = null;

        if (old('provisioning_id') !== null) {
            $selectedId = old('provisioning_id');
        } elseif ($request->has('instance_id')) {
            $selectedId = $request->input('instance_id'); 
        } else {
            $selectedId = $package->plugin_id;
        }

        $schema = [];
        $selectedPlugin = null;

        if ($selectedId) {
            $selectedPlugin = $provisionings->firstWhere('id', $selectedId);
            
            if ($selectedPlugin) {
                $instance = $manager->bootInstance($selectedPlugin);
                if ($instance && method_exists($instance, 'getPackageSchema')) {
                    $schema = $instance->getPackageSchema();
                }
            }
        }

        return view('admin::packages.provisioning.index', compact(
            'package',
            'provisionings',
            'selectedId',
            'schema',
            'selectedPlugin'
        ));
    }

    /**
     * Update the provisioning plugin and configuration for the specified package.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id, PluginManager $manager)
    {
        $package = Package::findOrFail($id);

        $oldPackage = $package->getOriginal();

        $validated = $request->validate([
            'provisioning_id' => ['nullable', 'exists:plugins,id'],
        ]);

        $configData = [];

        if ($validated['provisioning_id']) {
            $plugin = Plugin::findOrFail($validated['provisioning_id']);
            $configData = $this->validateConfig($request, $manager, $plugin);
        }

        $package->update([
            'plugin_id' => $validated['provisioning_id'] ?? null,
            'provisioning_config' => $configData,
        ]);

        $this->recordUpdate('package.provisioning.update', $oldPackage, $package->getChanges());

        return redirect()
            ->route('admin.packages.provisioning', ['id' => $package->id])
            ->with('success', __('common.update_success', ['attribute' => $package->name]));
    }

    /**
     * Validate provisioning configuration for the given plugin and request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\PluginManager $manager
     * @param \App\Models\Plugin $plugin
     * @return array<string, mixed>
     */
    private function validateConfig(Request $request, PluginManager $manager, Plugin $plugin): array
    {
        $instance = $manager->bootInstance($plugin);
        if (!$instance || !method_exists($instance, 'getPackageSchema')) {
            return [];
        }

        $schema = collect($instance->getPackageSchema());
        $prefix = "provisioning_config";

        $rules = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => $f['rules'] ?? 'nullable'])->all();
        $attrs = $schema->mapWithKeys(fn($f, $k) => ["{$prefix}.{$k}" => strtolower($f['label'] ?? $k)])->all();

        $request->validate($rules, [], $attrs);

        return $request->input($prefix, []);
    }
}