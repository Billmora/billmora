<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;

class ProvisioningController extends Controller
{
    /**
     * Create and activate the service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Service $service): RedirectResponse
    {
        if (!in_array($service->status, ['pending', 'terminated'])) {
            return back()->with('error', __('admin/services.provisioning.create.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service);

            $plugin->create($service, $instanceConfig);

            $service->activate();

            return back()->with('success', __('admin/services.provisioning.create.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/services.provisioning.create.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Suspend the active service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspend(Service $service): RedirectResponse
    {
        if ($service->status !== 'active') {
            return back()->with('error', __('admin/services.provisioning.suspend.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service);

            $plugin->suspend($service, $instanceConfig);

            $service->suspend();

            return back()->with('success', __('admin/services.provisioning.suspend.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/services.provisioning.suspend.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Unsuspend the suspended service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unsuspend(Service $service): RedirectResponse
    {
        if ($service->status !== 'suspended') {
            return back()->with('error', __('admin/services.provisioning.unsuspend.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service);

            $plugin->unsuspend($service, $instanceConfig);

            $service->unsuspend();

            return back()->with('success', __('admin/services.provisioning.unsuspend.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/services.provisioning.unsuspend.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Terminate the service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function terminate(Service $service): RedirectResponse
    {
        if (in_array($service->status, ['terminated', 'cancelled'])) {
            return back()->with('error', __('admin/services.provisioning.terminate.already_terminated'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service);
            $plugin->terminate($service, $instanceConfig);

            $service->terminate();

            return back()->with('success', __('admin/services.provisioning.terminate.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/services.provisioning.terminate.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Renew the service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function renew(Service $service): RedirectResponse
    {
        if (!in_array($service->status, ['active', 'suspended'])) {
            return back()->with('error', __('admin/services.provisioning.renew.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service);

            $plugin->renew($service, $instanceConfig);
            
            return back()->with('success', __('admin/services.provisioning.renew.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/services.provisioning.renew.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Scale the service to a different package on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function scale(Service $service): RedirectResponse
    {
        if ($service->status !== 'active') {
            return back()->with('error', __('admin/services.provisioning.scale.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service);

            $oldPackage = $service->getOriginal('package_id') 
                ? Package::find($service->getOriginal('package_id')) 
                : $service->package;

            $plugin->scale($service, $instanceConfig, $oldPackage);

            return back()->with('success', __('admin/services.provisioning.scale.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/services.provisioning.scale.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Get provisioning plugin instance and configuration for the service.
     *
     * @param \App\Models\Service $service
     * @return array{0: \App\Contracts\ProvisioningInterface, 1: array<string, mixed>}
     * 
     * @throws \Exception
     */
    private function getPluginAndConfig(Service $service): array
    {
        if (!$service->provisioning) {
            throw new \Exception(__('admin/services.provisioning.driver_missing'));
        }

        $plugin = $service->provisioning->getPluginInstance();

        if (!$plugin) {
            throw new \Exception(__('admin/services.provisioning.driver_class_missing', [
                'driver' => $service->provisioning->driver
            ]));
        }

        $instanceConfig = $service->provisioning->config ?? [];

        return [$plugin, $instanceConfig];
    }
}