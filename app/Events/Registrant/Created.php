<?php

namespace App\Events\Registrant;

use App\Models\Registrant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Created
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Registrant $registrant
    ) {}
}
