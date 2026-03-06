<?php

namespace App\Events\Ticket;

use App\Models\TicketMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Replied
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public TicketMessage $message) 
    {
        // 
    }
}