<?php

namespace App\Events\Service;

use App\Models\Service;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProvisioningFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(public Service $service, public string $errorMessage, public string $action = 'create') {}
}