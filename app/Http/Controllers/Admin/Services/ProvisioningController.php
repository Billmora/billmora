<?php

namespace App\Http\Controllers\Admin\Services;

use App\Events\Service as ServiceEvents;
use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Package;
use App\Services\ProvisioningService;
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
     * @param \App\Services\ProvisioningService $provisioningService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Service $service, ProvisioningService $provisioningService): RedirectResponse
    {
        if (!in_array($service->status, ['pending', 'terminated'])) {
            return back()->with('error', __('admin/services.provisioning.create.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);

            $plugin->create($service, $instanceConfig);

            $service->activate();

            Audit::system(Auth::user()->id, 'service.provisioning.create', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            return back()->with('success', __('admin/services.provisioning.create.success'));
        } catch (\Exception $e) {
            $this->logFailedAction('service.provisioning.create', $service, $e);

            return back()->with('error', __('admin/services.provisioning.create.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Suspend the active service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\ProvisioningService $provisioningService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspend(Service $service, ProvisioningService $provisioningService): RedirectResponse
    {
        if ($service->status !== 'active') {
            return back()->with('error', __('admin/services.provisioning.suspend.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);

            $plugin->suspend($service, $instanceConfig);

            $service->suspend();

            Audit::system(Auth::user()->id, 'service.provisioning.suspend', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            return back()->with('success', __('admin/services.provisioning.suspend.success'));
        } catch (\Exception $e) {
            $this->logFailedAction('service.provisioning.suspend', $service, $e);

            return back()->with('error', __('admin/services.provisioning.suspend.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Unsuspend the suspended service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\ProvisioningService $provisioningService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unsuspend(Service $service, ProvisioningService $provisioningService): RedirectResponse
    {
        if ($service->status !== 'suspended') {
            return back()->with('error', __('admin/services.provisioning.unsuspend.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);

            $plugin->unsuspend($service, $instanceConfig);

            $service->unsuspend();

            Audit::system(Auth::user()->id, 'service.provisioning.unsuspend', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            return back()->with('success', __('admin/services.provisioning.unsuspend.success'));
        } catch (\Exception $e) {
            $this->logFailedAction('service.provisioning.unsuspend', $service, $e);

            return back()->with('error', __('admin/services.provisioning.unsuspend.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Terminate the service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\ProvisioningService $provisioningService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function terminate(Service $service, ProvisioningService $provisioningService): RedirectResponse
    {
        if (in_array($service->status, ['terminated', 'cancelled'])) {
            return back()->with('error', __('admin/services.provisioning.terminate.already_terminated'));
        }

        try {
            [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);

            $plugin->terminate($service, $instanceConfig);

            $service->terminate();

            Audit::system(Auth::user()->id, 'service.provisioning.terminate', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            return back()->with('success', __('admin/services.provisioning.terminate.success'));
        } catch (\Exception $e) {
            $this->logFailedAction('service.provisioning.terminate', $service, $e);

            return back()->with('error', __('admin/services.provisioning.terminate.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Renew the service on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\ProvisioningService $provisioningService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function renew(Service $service, ProvisioningService $provisioningService): RedirectResponse
    {
        if (!in_array($service->status, ['active', 'suspended'])) {
            return back()->with('error', __('admin/services.provisioning.renew.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);

            $plugin->renew($service, $instanceConfig);

            Audit::system(Auth::user()->id, 'service.provisioning.renew', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            event(new ServiceEvents\ProvisioningRenewed($service));
            
            return back()->with('success', __('admin/services.provisioning.renew.success'));
        } catch (\Exception $e) {
            $this->logFailedAction('service.provisioning.renew', $service, $e);

            return back()->with('error', __('admin/services.provisioning.renew.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Scale the service to a different package on the provisioning provider.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\ProvisioningService $provisioningService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function scale(Service $service, ProvisioningService $provisioningService): RedirectResponse
    {
        if ($service->status !== 'active') {
            return back()->with('error', __('admin/services.provisioning.scale.invalid_status'));
        }

        try {
            [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);

            $oldPackage = $service->getOriginal('package_id') 
                ? Package::find($service->getOriginal('package_id')) 
                : $service->package;

            $plugin->scale($service, $instanceConfig, $oldPackage);

            Audit::system(Auth::user()->id, 'service.provisioning.scale', [
                'service_id' => $service->id,
                'status' => 'success',
            ]);

            event(new ServiceEvents\ProvisioningScaled($service));

            return back()->with('success', __('admin/services.provisioning.scale.success'));
        } catch (\Exception $e) {
            $this->logFailedAction('service.provisioning.scale', $service, $e);

            return back()->with('error', __('admin/services.provisioning.scale.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Log a failed provisioning action, ensuring no duplicates for the same service and event.
     *
     * @param string $event
     * @param Service $service
     * @param \Exception $e
     * @return void
     */
    protected function logFailedAction(string $event, Service $service, \Exception $e): void
    {
        $existing = \App\Models\AuditSystem::where('event', $event)
            ->where('properties->service_id', $service->id)
            ->where('properties->status', 'failed')
            ->first();

        if ($existing) {
            $properties = $existing->properties;
            $properties['message'] = $e->getMessage();
            $properties['attempts'] = ($properties['attempts'] ?? 1) + 1;
            
            $existing->update([
                'properties' => $properties,
                'created_at' => now(), // Bump timestamp so it appears at top of tasks
            ]);
        } else {
            Audit::system(Auth::user()->id, $event, [
                'service_id' => $service->id,
                'status' => 'failed',
                'message' => $e->getMessage(),
                'attempts' => 1,
            ]);
        }
    }
}