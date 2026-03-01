<?php

namespace App\Events\User;

use App\Models\UserBilling;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillingUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public UserBilling $billing;

    /**
     * Create a new event instance.
     */
    public function __construct(UserBilling $billing)
    {
        $this->billing = $billing;
    }
}