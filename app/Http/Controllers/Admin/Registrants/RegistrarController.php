<?php

namespace App\Http\Controllers\Admin\Registrants;

use App\Http\Controllers\Controller;
use App\Models\Registrant;
use App\Services\RegistrarService;
use App\Traits\AuditsSystem;

class RegistrarController extends Controller
{
    use AuditsSystem;

    public function __construct()
    {
        $this->middleware('permission:registrants.update');
    }

    /**
     * Trigger domain registration via registrar API.
     *
     * @param \App\Models\Registrant $registrant
     * @param \App\Services\RegistrarService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Registrant $registrant, RegistrarService $service)
    {
        if (!in_array($registrant->status, ['pending', 'terminated'])) {
            return back()->with('error', __('admin/registrants.registrar.create.invalid_status'));
        }

        try {
            [$plugin, $config] = $service->bootPluginFor($registrant);
            $plugin->create($registrant);

            $registrant->update([
                'status' => 'active',
                'registered_at' => now(),
                'expires_at' => now()->addYears($registrant->years),
            ]);

            $this->recordCreate('registrant.registrar.create', ['domain' => $registrant->domain]);

            return back()->with('success', __('admin/registrants.registrar.create.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.create.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Trigger domain transfer via registrar API.
     *
     * @param \App\Models\Registrant $registrant
     * @param \App\Services\RegistrarService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function transfer(Registrant $registrant, RegistrarService $service)
    {
        if ($registrant->status !== 'pending_transfer') {
            return back()->with('error', __('admin/registrants.registrar.transfer.invalid_status'));
        }

        try {
            [$plugin, $config] = $service->bootPluginFor($registrant);
            $plugin->transfer($registrant, $registrant->epp_code ?? '');

            $this->recordCreate('registrant.registrar.transfer', ['domain' => $registrant->domain]);

            return back()->with('success', __('admin/registrants.registrar.transfer.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.transfer.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Trigger domain renewal via registrar API.
     *
     * @param \App\Models\Registrant $registrant
     * @param \App\Services\RegistrarService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function renew(Registrant $registrant, RegistrarService $service)
    {
        if (!in_array($registrant->status, ['active', 'expired'])) {
            return back()->with('error', __('admin/registrants.registrar.renew.invalid_status'));
        }

        try {
            [$plugin, $config] = $service->bootPluginFor($registrant);
            $plugin->renew($registrant, $registrant->years);

            $registrant->update([
                'status' => 'active',
                'expires_at' => ($registrant->expires_at ?? now())->addYears($registrant->years),
            ]);

            $this->recordCreate('registrant.registrar.renew', ['domain' => $registrant->domain]);

            return back()->with('success', __('admin/registrants.registrar.renew.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.renew.failed', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Sync domain status from registrar API.
     *
     * @param \App\Models\Registrant $registrant
     * @param \App\Services\RegistrarService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sync(Registrant $registrant, RegistrarService $service)
    {
        try {
            [$plugin, $config] = $service->bootPluginFor($registrant);
            $result = $plugin->syncStatus($registrant);

            $registrant->update([
                'status' => $result['status'] ?? $registrant->status,
                'expires_at' => $result['expires_at'] ?? $registrant->expires_at,
            ]);

            return back()->with('success', __('admin/registrants.registrar.sync.success'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin/registrants.registrar.sync.failed', ['message' => $e->getMessage()]));
        }
    }
}
