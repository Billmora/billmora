<?php

namespace App\Events\Ticket;

use App\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Assigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public Ticket $ticket) {}
}