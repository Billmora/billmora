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
                    // Re-decrement stock only if reactivated from cancelled or terminated state.
                    // Initial orders already decrement stock at creation time.
                    if (in_array($oldStatus, ['cancelled', 'terminated'])) {
                        if ($service->package && $service->package->stock > 0) {
                            \App\Models\Package::where('id', $service->package_id)
                                ->where('stock', '>', 0)
                                ->decrement('stock');
                        }
                    }

                    event(new ServiceEvents\ProvisioningActivated($service));
                }

                if ($service->order && $service->order->status !== 'completed') {
                    $uncompletedServicesCount = $service->order->services()->where('status', '!=', 'active')->count();

                    if ($uncompletedServicesCount === 0) {
                        $service->order->markAsCompleted();
                    }
                }
            } elseif ($newStatus === 'pending') {
                // Re-decrement stock if restored from cancelled or terminated back to pending
                if (in_array($oldStatus, ['cancelled', 'terminated'])) {
                    if ($service->package && $service->package->stock > 0) {
                        \App\Models\Package::where('id', $service->package_id)
                            ->where('stock', '>', 0)
                            ->decrement('stock');
                    }
                }
            } elseif ($newStatus === 'suspended') {
                event(new ServiceEvents\ProvisioningSuspended($service));
            } elseif (in_array($newStatus, ['terminated', 'cancelled'])) {
                // Only restore stock if not already cancelled or terminated
                if (!in_array($oldStatus, ['terminated', 'cancelled'])) {
                    if ($service->package && $service->package->stock >= 0) {
                        \App\Models\Package::where('id', $service->package_id)
                            ->where('stock', '>=', 0)
                            ->increment('stock');
                    }
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
        // Restore stock if deleted while active, pending, or suspended
        if (!in_array($service->status, ['terminated', 'cancelled'])) {
            if ($service->package && $service->package->stock >= 0) {
                \App\Models\Package::where('id', $service->package_id)
                    ->where('stock', '>=', 0)
                    ->increment('stock');
            }
        }
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
