<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\Plugin;
use App\Support\GatewayCallbackResponse;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCaptured
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Invoice $invoice The invoice that was paid
     * @param \App\Models\Plugin $plugin The gateway plugin used
     * @param \App\Support\GatewayCallbackResponse $response The standardized gateway response
     */
    public function __construct(
        public readonly Invoice $invoice,
        public readonly Plugin $plugin,
        public readonly GatewayCallbackResponse $response
    ) {}
}
