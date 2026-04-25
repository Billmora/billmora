<?php

namespace App\Events\Domain;

use App\Models\Registrant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistrationFailed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Registrant $registrant,
        public string $reason
    ) {}
}
