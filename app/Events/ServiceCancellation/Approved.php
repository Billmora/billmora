<?php

namespace App\Events\ServiceCancellation;

use App\Models\ServiceCancellation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Approved
{
    use Dispatchable, SerializesModels;

    public function __construct(public ServiceCancellation $cancellation) {}
}