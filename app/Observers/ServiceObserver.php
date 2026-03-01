<?php

namespace App\Observers;

use App\Events\Service as ServiceEvents;
use App\Models\Service;

class ServiceObserver
{
    /**
     * Handle the Service "created" event.
     */
    public function created(Service $service): void
    {
        event(new ServiceEvents\Created($service));
    }

    /**
     * Handle the Service "updated" event.
     */
    public function updated(Service $service): void
    {
        if ($service->wasChanged('status')) {
            $oldStatus = $service->getOriginal('status');
            $newStatus = $service->status;

            if ($newStatus === 'active') {
                if ($oldStatus === 'suspended') {
                    event(new ServiceEvents\ProvisioningUnsuspended($service));
                } else {
                    event(new ServiceEvents\ProvisioningActivated($service));
                }
            } elseif ($newStatus === 'suspended') {
                event(new ServiceEvents\ProvisioningSuspended($service));
            } elseif (in_array($newStatus, ['terminated', 'cancelled'])) {
                event(new ServiceEvents\ProvisioningTerminated($service));
            }
        }
    }

    /**
     * Handle the Service "deleted" event.
     */
    public function deleted(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "restored" event.
     */
    public function restored(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "force deleted" event.
     */
    public function forceDeleted(Service $service): void
    {
        //
    }
}
