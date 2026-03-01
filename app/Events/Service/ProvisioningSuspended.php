<?php

namespace App\Events\Service;

use App\Models\Service;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProvisioningSuspended
{
    use Dispatchable, SerializesModels;

    public function __construct(public Service $service) {}
}