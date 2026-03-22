<?php

namespace App\Contracts;

use App\Models\Invoice;
use Illuminate\Http\Request;

interface GatewayInterface extends PluginInterface
{
    /**
     * Check if the gateway is applicable for the given amount and currency.
     *
     * @param float $amount
     * @param string $currency ISO 4217 currency code (e.g., 'IDR', 'USD')
     * @return bool
     */
    public function isApplicable(float $amount, string $currency): bool;

   /**
     * Process the payment request and return gateway response.
     *
     * @param string $invoiceNumber
     * @param float $amount
     * @param string $currency ISO 4217 currency code (e.g., 'IDR', 'USD')
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function pay(string $invoiceNumber, float $amount, string $currency, array $options = []);

    /**
     * Handle explicit background server-to-server webhook (stateless).
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Support\GatewayCallbackResponse
     */
    public function webhook(Request $request): \App\Support\GatewayCallbackResponse;

    /**
     * Handle explicit browser return redirect (stateful).
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Support\GatewayCallbackResponse
     */
    public function return(Request $request): \App\Support\GatewayCallbackResponse;
}