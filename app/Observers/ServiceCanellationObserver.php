<?php

namespace App\Observers;

use App\Events\ServiceCancellation as ServiceCancellationEvents;
use App\Models\ServiceCancellation;

class ServiceCanellationObserver
{
    /**
     * Handle the ServiceCancellation "created" event.
     */
    public function created(ServiceCancellation $serviceCancellation): void
    {
        event(new ServiceCancellationEvents\Requested($serviceCancellation));
    }

    /**
     * Handle the ServiceCancellation "updated" event.
     */
    public function updated(ServiceCancellation $serviceCancellation): void
    {
        if ($serviceCancellation->wasChanged('status')) {
            if ($serviceCancellation->status === 'approved') {
                event(new ServiceCancellationEvents\Approved($serviceCancellation));
            } elseif ($serviceCancellation->status === 'rejected') {
                event(new ServiceCancellationEvents\Rejected($serviceCancellation));
            }
        }
    }

    /**
     * Handle the ServiceCancellation "deleted" event.
     */
    public function deleted(ServiceCancellation $serviceCancellation): void
    {
        //
    }

    /**
     * Handle the ServiceCancellation "restored" event.
     */
    public function restored(ServiceCancellation $serviceCancellation): void
    {
        //
    }

    /**
     * Handle the ServiceCancellation "force deleted" event.
     */
    public function forceDeleted(ServiceCancellation $serviceCancellation): void
    {
        //
    }
}
