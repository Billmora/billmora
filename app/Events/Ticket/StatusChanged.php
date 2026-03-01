<?php

namespace App\Events\Ticket;

use App\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(public Ticket $ticket, public string $oldStatus, public string $newStatus) {}
}