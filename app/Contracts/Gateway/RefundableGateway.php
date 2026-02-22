<?php

namespace App\Contracts\Gateway;

interface RefundableGateway
{
    /**
     * Process a refund back to the customer's payment method.
     *
     * @param string $transactionId
     * @param float $amount
     * @param string $currency
     * @return bool
     */
    public function refund(string $transactionId, float $amount, string $currency): bool;
}
