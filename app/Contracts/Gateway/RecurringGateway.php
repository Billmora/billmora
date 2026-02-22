<?php

namespace App\Contracts\Gateway;

interface RecurringGateway
{
    /**
     * Auto-capture payment using a saved token.
     *
     * @param string $paymentToken
     * @param float $amount
     * @param string $currency
     * @return bool
     */
    public function capture(string $paymentToken, float $amount, string $currency): bool;

    /**
     * Cancel an active subscription profile on the gateway side.
     *
     * @param string $subscriptionId
     * @return bool
     */
    public function cancelSubscription(string $subscriptionId): bool;
}
