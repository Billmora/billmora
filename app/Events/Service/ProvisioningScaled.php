<?php

namespace App\Events\Service;

use App\Models\Service;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProvisioningScaled
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Service $service, public $oldPackage = null) 
    {
        // 
    }
}