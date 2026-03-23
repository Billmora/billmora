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
                    if ($service->package && $service->package->stock > 0) {
                        $service->package->decrement('stock');
                    }
                    
                    event(new ServiceEvents\ProvisioningActivated($service));
                }

                if ($service->order && $service->order->status !== 'completed') {
                    $uncompletedServicesCount = $service->order->services()->where('status', '!=', 'active')->count();
                    
                    if ($uncompletedServicesCount === 0) {
                        $service->order->markAsCompleted();
                    }
                }
            } elseif ($newStatus === 'suspended') {
                event(new ServiceEvents\ProvisioningSuspended($service));
            } elseif (in_array($newStatus, ['terminated', 'cancelled'])) {
                if ($service->package && $service->package->stock >= 0) {
                    $service->package->increment('stock');
                }
                
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
