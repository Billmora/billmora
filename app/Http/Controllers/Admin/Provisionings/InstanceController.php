<?php

namespace App\Http\Controllers\Admin\Provisionings;

use App\Http\Controllers\Controller;
use App\Models\Provisioning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class InstanceController extends Controller
{
    /**
     * Display all instances for the specified provisioning driver.
     *
     * @param string $driver
     * @return \Illuminate\View\View
     */
    public function index($driver)
    {
        if (!File::exists(base_path("plugin/Provisioning/{$driver}"))) {
            abort(404, __('admin/provisionings.instance.driver_not_found', ['driver' => $driver]));
        }

        $instances = Provisioning::where('driver', $driver)->latest()->get();

        return view('admin::provisionings.show', compact('instances', 'driver'));
    }

    /**
     * Show the form for creating a new provisioning instance.
     *
     * @param string $driver
     * @return \Illuminate\View\View
     */
    public function create($driver)
    {
        $className = $this->getPluginClass($driver);
        
        $formFields = $className::getConfig();

        return view('admin::provisionings.instances.create', compact('driver', 'formFields'));
    }

    /**
     * Store a newly created provisioning instance.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $driver)
    {
        $className = $this->getPluginClass($driver);
        
        $configFields = $className::getConfig();

        $rules = [
            'instance_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('provisionings', 'name')->where(function ($query) use ($driver) {
                    return $query->where('driver', $driver);
                }),
            ],
            'instance_active' => ['boolean'],
        ];

        foreach ($configFields as $key => $field) {
            if (isset($field['rules'])) {
                $rules[$key] = $field['rules'];
            }
        }

        $validated = $request->validate($rules);

        $config = $request->only(array_keys($configFields));

        $instance = Provisioning::create([
            'name' => $validated['instance_name'],
            'driver' => $driver,
            'is_active' => (bool) $validated['instance_active'],
            'config' => (array) $config,
        ]);

        return redirect()->route('admin.provisionings.instance.edit', ['driver' => $driver, 'instance' => $instance->id])
            ->with('success', __('common.create_success', ['attribute' => $instance->name]));
    }

    /**
     * Show the form for editing the specified provisioning instance.
     *
     * @param string $driver
     * @param \App\Models\Provisioning $instance
     * @return \Illuminate\View\View
     */
    public function edit($driver, Provisioning $instance)
    {
        if ($instance->driver !== $driver) {
            abort(404);
        }

        $className = $this->getPluginClass($driver);
        $formFields = $className::getConfig();

        return view('admin::provisionings.instances.edit', compact('driver', 'instance', 'formFields'));
    }

    /**
     * Update the specified provisioning instance.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $driver
     * @param \App\Models\Provisioning $instance
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $driver, Provisioning $instance)
    {
        if ($instance->driver !== $driver) {
            abort(404);
        }

        $className = $this->getPluginClass($driver);
        $configFields = $className::getConfig();

        $rules = [
            'instance_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('provisionings', 'name')->where(function ($query) use ($driver) {
                    return $query->where('driver', $driver);
                })->ignore($instance->id),
            ],
            'instance_active' => ['boolean'], 
        ];

        foreach ($configFields as $key => $field) {
            if (isset($field['rules'])) {
                $rules[$key] = $field['rules'];
            }
        }

        $validated = $request->validate($rules);

        $config = $request->only(array_keys($configFields));

        $instance->update([
            'name' => $validated['instance_name'],
            'is_active' => (bool) $validated['instance_active'],
            'config' => (array) $config, 
        ]);

        return redirect()
            ->route('admin.provisionings.instance', $driver)
            ->with('success', __('common.update_success', ['attribute' => $instance->name]));
    }

    /**
     * Remove the specified provisioning instance.
     *
     * @param string $driver
     * @param \App\Models\Provisioning $instance
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($driver, Provisioning $instance)
    {
        if ($instance->driver !== $driver) {
            abort(404);
        }

        $activeService = $instance->services()->count();

        if ($activeService > 0) {
            return back()->with('error', __('admin/provisionings.instance.delete_in_use', [
                'name' => $instance->name, 
                'count' => $activeService
            ]));
        }

        $instance->delete();

        return redirect()
            ->route('admin.provisionings.instance', $driver)
            ->with('success', __('common.delete_success', ['attribute' => $instance->name]));
    }

    /**
     * Test connection to provisioning instance.
     *
     * @param string $driver
     * @param \App\Models\Provisioning $instance
     * @return \Illuminate\Http\RedirectResponse
     */
    public function testConnection($driver, Provisioning $instance)
    {
        if ($instance->driver !== $driver) abort(404);

        try {
            $plugin = $instance->getPluginInstance();
            
            $plugin->testConnection($instance->config);

            return back()->with('success', __('admin/provisionings.connection.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/provisionings.connection.failed', ['message' => $e->getMessage()]) );
        }
    }

    /**
     * Get the plugin class for the specified driver.
     *
     * @param string $driver
     * @return string
     */
    private function getPluginClass($driver)
    {
        $className = "Plugin\\Provisioning\\{$driver}\\{$driver}";
        
        if (!class_exists($className)) 
        {
            return abort(500, __('admin/provisionings.instance.class_not_found', ['name' => $className]));
        }

        return $className;
    }
}
