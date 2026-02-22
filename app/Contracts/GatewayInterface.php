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
     * @param string $transactionId
     * @param float $amount
     * @param string $currency ISO 4217 currency code (e.g., 'IDR', 'USD')
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function pay(string $transactionId, float $amount, string $currency, array $options = []);

    /**
     * Handle payment gateway callback and update invoice status.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function callback(Request $request);
}