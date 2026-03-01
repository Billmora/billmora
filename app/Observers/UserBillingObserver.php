<?php

namespace App\Observers;

use App\Events\User as UserEvents;
use App\Models\UserBilling;

class UserBillingObserver
{
    /**
     * Handle the UserBilling "created" event.
     */
    public function created(UserBilling $userBilling): void
    {
        event(new UserEvents\BillingUpdated($userBilling));
    }

    /**
     * Handle the UserBilling "updated" event.
     */
    public function updated(UserBilling $userBilling): void
    {
        if ($userBilling->wasChanged([
            'phone_number', 'company_name', 'street_address_1', 
            'street_address_2', 'city', 'country', 'state', 'postcode'
        ])) {
            event(new UserEvents\BillingUpdated($userBilling));
        }
    }

    /**
     * Handle the UserBilling "deleted" event.
     */
    public function deleted(UserBilling $userBilling): void
    {
        //
    }

    /**
     * Handle the UserBilling "restored" event.
     */
    public function restored(UserBilling $userBilling): void
    {
        //
    }

    /**
     * Handle the UserBilling "force deleted" event.
     */
    public function forceDeleted(UserBilling $userBilling): void
    {
        //
    }
}
