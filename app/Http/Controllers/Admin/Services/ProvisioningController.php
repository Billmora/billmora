<?php

namespace App\Http\Controllers\Admin\Services;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Package;
use App\Services\PluginManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProvisioningController extends Controller
{
    /**
     * Applies permission-based middleware for accessing action services.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:services.update')->only(['create', 'suspend', 'unsuspend', 'terminate', 'renew', 'scale']);
    }

    /**
     * Create and activate the service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Service $service, PluginManager $manager): RedirectResponse
    {
        if (!in_array($service->status, ['pending', 'terminated'])) {
            return back()->with('error', __('admin/services.provisioning.create.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service, $manager);

            $plugin->create($service, $instanceConfig);

            $service->activate();

            Audit::system(Auth::user()->id, 'service.provisioning.create', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            return back()->with('success', __('admin/services.provisioning.create.success'));
        } catch (\Exception $e) {
            Audit::system(Auth::user()->id, 'service.provisioning.create', [
                'service_id' => $service->id,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', __('admin/services.provisioning.create.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Suspend the active service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspend(Service $service, PluginManager $manager): RedirectResponse
    {
        if ($service->status !== 'active') {
            return back()->with('error', __('admin/services.provisioning.suspend.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service, $manager);

            $plugin->suspend($service, $instanceConfig);

            $service->suspend();

            Audit::system(Auth::user()->id, 'service.provisioning.suspend', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            return back()->with('success', __('admin/services.provisioning.suspend.success'));
        } catch (\Exception $e) {
            Audit::system(Auth::user()->id, 'service.provisioning.suspend', [
                'service_id' => $service->id,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', __('admin/services.provisioning.suspend.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Unsuspend the suspended service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unsuspend(Service $service, PluginManager $manager): RedirectResponse
    {
        if ($service->status !== 'suspended') {
            return back()->with('error', __('admin/services.provisioning.unsuspend.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service, $manager);

            $plugin->unsuspend($service, $instanceConfig);

            $service->unsuspend();

            Audit::system(Auth::user()->id, 'service.provisioning.unsuspend', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            return back()->with('success', __('admin/services.provisioning.unsuspend.success'));
        } catch (\Exception $e) {
            Audit::system(Auth::user()->id, 'service.provisioning.unsuspend', [
                'service_id' => $service->id,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', __('admin/services.provisioning.unsuspend.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Terminate the service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function terminate(Service $service, PluginManager $manager): RedirectResponse
    {
        if (in_array($service->status, ['terminated', 'cancelled'])) {
            return back()->with('error', __('admin/services.provisioning.terminate.already_terminated'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service, $manager);
            $plugin->terminate($service, $instanceConfig);

            $service->terminate();

            Audit::system(Auth::user()->id, 'service.provisioning.terminate', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            return back()->with('success', __('admin/services.provisioning.terminate.success'));
        } catch (\Exception $e) {
            Audit::system(Auth::user()->id, 'service.provisioning.terminate', [
                'service_id' => $service->id,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', __('admin/services.provisioning.terminate.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Renew the service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function renew(Service $service, PluginManager $manager): RedirectResponse
    {
        if (!in_array($service->status, ['active', 'suspended'])) {
            return back()->with('error', __('admin/services.provisioning.renew.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service, $manager);

            $plugin->renew($service, $instanceConfig);

            Audit::system(Auth::user()->id, 'service.provisioning.renew', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);
            
            return back()->with('success', __('admin/services.provisioning.renew.success'));
        } catch (\Exception $e) {
            Audit::system(Auth::user()->id, 'service.provisioning.renew', [
                'service_id' => $service->id,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', __('admin/services.provisioning.renew.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Scale the service to a different package on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function scale(Service $service, PluginManager $manager): RedirectResponse
    {
        if ($service->status !== 'active') {
            return back()->with('error', __('admin/services.provisioning.scale.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $this->getPluginAndConfig($service, $manager);

            $oldPackage = $service->getOriginal('package_id') 
                ? Package::find($service->getOriginal('package_id')) 
                : $service->package;

            $plugin->scale($service, $instanceConfig, $oldPackage);

            Audit::system(Auth::user()->id, 'service.provisioning.scale', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            return back()->with('success', __('admin/services.provisioning.scale.success'));
        } catch (\Exception $e) {
            Audit::system(Auth::user()->id, 'service.provisioning.scale', [
                'service_id' => $service->id,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', __('admin/services.provisioning.scale.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Get booted provisioning plugin instance and configuration for the service.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\PluginManager $manager
     * @return array{0: \App\Contracts\ProvisioningInterface, 1: array<string, mixed>}
     *
     * @throws \Exception
     */
    private function getPluginAndConfig(Service $service, PluginManager $manager): array
    {
        if (!$service->provisioning) {
            throw new \Exception(__('admin/services.provisioning.provider_missing'));
        }

        if (!$service->provisioning->is_active) {
            throw new \Exception(__('validation.provisioning_disabled', ['name' => $service->provisioning->name]));
        }

        $plugin = $manager->bootInstance($service->provisioning);

        if (!$plugin) {
            throw new \Exception(__('admin/services.provisioning.provider_class_missing', [
                'provider' => $service->provisioning->provider
            ]));
        }

        $instanceConfig = $service->provisioning->config ?? [];

        return [$plugin, $instanceConfig];
    }
}