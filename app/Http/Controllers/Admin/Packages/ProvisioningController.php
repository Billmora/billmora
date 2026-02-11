<?php

namespace App\Http\Controllers\Admin\Packages;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Provisioning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProvisioningController extends Controller
{

    /**
     * Display package provisioning configuration page with available drivers and instances.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $drivers = [];
        $path = base_path('plugin/Provisioning');
        if (File::exists($path)) {
            foreach (File::directories($path) as $dir) {
                $driverName = basename($dir);
                $drivers[$driverName] = $driverName;
            }
        }

        $selectedDriver = $this->resolveDriver($request->get('driver', $package->provisioning_driver));
        $selectedInstanceId = $request->get('instance', $package->provisioning_id);

        $instances = $selectedDriver
            ? Provisioning::where('driver', $selectedDriver)
                          ->where('is_active', true)
                          ->pluck('name', 'id')
            : collect();

        $formFields = $selectedDriver ? $this->getFormFields($selectedDriver, $selectedInstanceId) : [];

        return view('admin::packages.provisioning.index', compact(
            'package',
            'drivers',
            'instances',
            'selectedDriver',
            'selectedInstanceId',
            'formFields'
        ));
    }

    /**
     * Update package provisioning configuration including driver and package-specific fields.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $validated = $request->validate([
            'provisioning_driver' => ['required', 'string'],
            'instance_reference'  => ['nullable', Rule::exists('provisionings', 'id')],
        ]);

        $rawDriver = $validated['provisioning_driver'];
        $instanceId = ($rawDriver === 'none') ? null : $validated['instance_reference'];
        $driver = ($rawDriver === 'none') ? null : $this->resolveDriver($rawDriver);

        $configData = [];
        if ($driver) {
            $configData = $this->validateAndExtractConfig($request, $driver);
        }

        $package->update([
            'provisioning_driver' => $driver,
            'provisioning_id' => $instanceId,
            'provisioning_config' => $configData,
        ]);

        return redirect()->route('admin.packages.provisioning', [
            'id' => $id,
            'driver' => $driver ?? 'none',
        ])->with('success', __('common.update_success', ['attribute' => $package->name]));
    }

    /**
     * Resolve provision driver from request, mapping 'none' to null.
     *
     * @param string|null $driver
     * @return string|null
     */
    private function resolveDriver(?string $driver)
    {
        return $driver === 'none' ? null : $driver;
    }

    /**
     * Get package configuration fields for selected driver and instance.
     *
     * @param string $driver
     * @param string|null $instanceId
     * @return array
     */
    private function getFormFields(string $driver, ?string $instanceId)
    {
        $className = "Plugin\\Provisioning\\{$driver}\\{$driver}";
        if (! class_exists($className)) {
            return [];
        }

        $plugin = new $className();
        $instanceObj = $instanceId ? Provisioning::find($instanceId) : null;

        return $plugin->getPackageFields($instanceObj->config) ?: [];
    }

    /**
     * Validate and extract provisioning configuration data for driver.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $driver
     * @return array
     */
    private function validateAndExtractConfig(Request $request, string $driver)
    {
        $className = "Plugin\\Provisioning\\{$driver}\\{$driver}";
        if (! class_exists($className)) {
            return back()
                ->withInput()
                ->with('error', __('admin/packages.provisioning.plugin_not_found', ['driver' => $driver]))
                ->throwResponse();
        }

        $plugin = new $className();
        $fields = $plugin->getPackageFields(null);

        $rules = [];
        $labels = [];

        foreach ($fields as $key => $field) {
            if (isset($field['rules'])) {
                $rules["config.{$key}"] = $field['rules'];
                $labels["config.{$key}"] = $field['label'] ?? $key;
            }
        }

        $validator = Validator::make($request->all(), $rules, [], $labels);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->throwResponse();
        }

        $validData = $validator->validated();

        return $validData['config'] ?? [];
    }
}