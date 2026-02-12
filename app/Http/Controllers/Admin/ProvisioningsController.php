<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provisioning;
use App\Services\PluginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProvisioningsController extends Controller
{
    /**
     * Applies permission-based middleware for accessing provisionings plugin.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:provisionings.view')->only(['index']);
        $this->middleware('permission:provisionings.install')->only(['install']);
        $this->middleware('permission:provisionings.uninstall')->only(['uninstall']);
    }

    /**
     * Display a list of available provisioning plugins.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $provisionings = [];
        $path = base_path('plugin/Provisioning');

        if (File::exists($path)) {
            foreach (File::directories($path) as $dir) {
                $driverName = basename($dir);
                $manifestPath = $dir . '/manifest.json';

                if (!File::exists($manifestPath)) continue;

                $manifest = json_decode(File::get($manifestPath), true);

                $provisionings[] = (object) [
                    'driver' => $driverName,
                    'name' => $manifest['name'] ?? $driverName,
                    'version' => $manifest['version'] ?? '0.0.0',
                    'description' => $manifest['description'] ?? '-',
                    'author' => $manifest['author'] ?? 'Unknown',
                    'instance_count' => Provisioning::where('driver', $driverName)->count(),
                ];
            }
        }

        if ($search = $request->get('search')) {
            $provisionings = collect($provisionings)->filter(function ($item) use ($search) {
                return stripos($item->name, $search) !== false ||
                    stripos($item->driver, $search) !== false ||
                    stripos($item->description, $search) !== false ||
                    stripos($item->author, $search) !== false;
            })->values();
        }

        return view('admin::provisionings.index', compact('provisionings'));
    }

    /**
     * Install a new provisioning plugin from uploaded ZIP file.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\PluginService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function install(Request $request, PluginService $installer)
    {
        $validator = Validator::make($request->all(), [
            'plugin_file' => ['required', 'file', 'mimes:zip', 'max:10240']
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->errors()->first());
        }

        try {
            $metadata = $installer->install($request->file('plugin_file'), 'provisioning');
            
            return redirect()->back()->with('success', __('admin/provisionings.install.success', [
                'name' => $metadata['name'], 
                'version' => $metadata['version']
            ]));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Uninstall a provisioning plugin driver.
     *
     * @param string $driver
     * @param \App\Services\PluginService $pluginService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uninstall($driver, PluginService $pluginService)
    {
        $instance = Provisioning::where('driver', $driver)->count();

        if ($instance > 0) {
            return back()->with('error', __('admin/provisionings.uninstall.active_instances', [
                'driver' => $driver, 
                'count' => $instance
            ]));
        }

        try {
            $pluginService->uninstall($driver, 'provisioning');
            
            return redirect()->route('admin.provisionings')
                ->with('success', __('common.uninstall_success', ['attribute' => $driver]));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
