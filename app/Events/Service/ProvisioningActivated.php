<?php

namespace App\Events\Service;

use App\Models\Service;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProvisioningActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Service $service) {}
}