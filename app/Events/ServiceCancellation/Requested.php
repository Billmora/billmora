<?php

namespace App\Events\ServiceCancellation;

use App\Models\ServiceCancellation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Requested
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ServiceCancellation $cancellation) 
    {
        // 
    }
}