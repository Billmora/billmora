<?php

namespace App\Http\Controllers\Admin\Registrants;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Models\Registrant;
use App\Services\RegistrarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegistrarController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:registrants.update');
    }

    /**
     * Trigger domain registration via registrar API.
     */
    public function create(Registrant $registrant, RegistrarService $service): RedirectResponse
    {
        if (!in_array($registrant->status, ['pending', 'terminated'])) {
            return back()->with('error', __('admin/registrants.registrar.create.invalid_status'));
        }

        try {
            [$plugin] = $service->bootPluginFor($registrant);
            $plugin->create($registrant);
            $registrant->activate();

            Audit::system(Auth::user()->id, 'registrant.registrar.create', [
                'registrant_id' => $registrant->id,
                'domain'        => $registrant->domain,
                'status'        => 'success',
            ]);

            return back()->with('success', __('admin/registrants.registrar.create.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.create.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Trigger domain transfer via registrar API.
     * EPP code is read from the original order item config options.
     */
    public function transfer(Registrant $registrant, RegistrarService $service): RedirectResponse
    {
        if ($registrant->status !== 'pending_transfer') {
            return back()->with('error', __('admin/registrants.registrar.transfer.invalid_status'));
        }

        try {
            [$plugin] = $service->bootPluginFor($registrant);
            $eppCode = $registrant->orderItem?->config_options['epp_code'] ?? '';
            $plugin->transfer($registrant, $eppCode);

            $registrant->update(['status' => 'pending_transfer', 'registered_at' => now()]);

            Audit::system(Auth::user()->id, 'registrant.registrar.transfer', [
                'registrant_id' => $registrant->id,
                'domain'        => $registrant->domain,
                'status'        => 'success',
            ]);

            return back()->with('success', __('admin/registrants.registrar.transfer.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.transfer.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Trigger domain renewal via registrar API.
     */
    public function renew(Registrant $registrant, RegistrarService $service): RedirectResponse
    {
        if (!in_array($registrant->status, ['active', 'expired'])) {
            return back()->with('error', __('admin/registrants.registrar.renew.invalid_status'));
        }

        try {
            [$plugin] = $service->bootPluginFor($registrant);
            $plugin->renew($registrant, $registrant->years);

            $registrant->update([
                'status'     => 'active',
                'expires_at' => ($registrant->expires_at ?? now())->addYears($registrant->years),
            ]);

            Audit::system(Auth::user()->id, 'registrant.registrar.renew', [
                'registrant_id' => $registrant->id,
                'domain'        => $registrant->domain,
                'status'        => 'success',
            ]);

            return back()->with('success', __('admin/registrants.registrar.renew.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.renew.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Suspend the domain on the registrar.
     */
    public function suspend(Registrant $registrant, RegistrarService $service): RedirectResponse
    {
        if ($registrant->status !== 'active') {
            return back()->with('error', __('admin/registrants.registrar.suspend.invalid_status'));
        }

        try {
            [$plugin] = $service->bootPluginFor($registrant);

            if (method_exists($plugin, 'suspend')) {
                $plugin->suspend($registrant);
            }

            $registrant->suspend();

            Audit::system(Auth::user()->id, 'registrant.registrar.suspend', [
                'registrant_id' => $registrant->id,
                'domain'        => $registrant->domain,
                'status'        => 'success',
            ]);

            return back()->with('success', __('admin/registrants.registrar.suspend.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.suspend.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Unsuspend the domain on the registrar.
     */
    public function unsuspend(Registrant $registrant, RegistrarService $service): RedirectResponse
    {
        if ($registrant->status !== 'suspended') {
            return back()->with('error', __('admin/registrants.registrar.unsuspend.invalid_status'));
        }

        try {
            [$plugin] = $service->bootPluginFor($registrant);

            if (method_exists($plugin, 'unsuspend')) {
                $plugin->unsuspend($registrant);
            }

            $registrant->unsuspend();

            Audit::system(Auth::user()->id, 'registrant.registrar.unsuspend', [
                'registrant_id' => $registrant->id,
                'domain'        => $registrant->domain,
                'status'        => 'success',
            ]);

            return back()->with('success', __('admin/registrants.registrar.unsuspend.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.unsuspend.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Sync domain status from registrar API.
     */
    public function sync(Registrant $registrant, RegistrarService $service): RedirectResponse
    {
        try {
            [$plugin] = $service->bootPluginFor($registrant);
            $result = $plugin->syncStatus($registrant);

            $registrant->update([
                'status'     => $result['status'] ?? $registrant->status,
                'expires_at' => $result['expires_at'] ?? $registrant->expires_at,
            ]);

            Audit::system(Auth::user()->id, 'registrant.registrar.sync', [
                'registrant_id' => $registrant->id,
                'domain'        => $registrant->domain,
                'status'        => 'success',
            ]);

            return back()->with('success', __('admin/registrants.registrar.sync.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.sync.failed', ['message' => $e->getMessage()]));
        }
    }
}
