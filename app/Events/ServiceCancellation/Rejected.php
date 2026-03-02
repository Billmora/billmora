<?php

namespace App\Events\ServiceCancellation;

use App\Models\ServiceCancellation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Rejected
{
    use Dispatchable, SerializesModels;

    public function __construct(public ServiceCancellation $cancellation) {}
}