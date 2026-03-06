<?php

namespace App\Events\Invoice;

use App\Models\Invoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Created
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice, public bool $sendEmail = true)
    {
        $this->invoice = $invoice;
    }
}