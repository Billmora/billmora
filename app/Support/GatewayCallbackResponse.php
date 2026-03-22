<?php

namespace App\Support;

class GatewayCallbackResponse
{
    protected bool $isValid;
    protected bool $isSuccess;
    protected string $orderNumber;
    protected ?string $gatewayReference;
    protected float $amount;
    protected float $fee;
    protected ?string $redirectUrl;
    /**
     * Create a new Gateway Callback Response instance.
     *
     * @param bool $isValid Indicates if the callback signature and payload are valid
     * @param bool $isSuccess Indicates if the payment was successfully captured
     * @param string $orderNumber The invoice or order number
     * @param string|null $gatewayReference The unique transaction reference from the gateway
     * @param float $amount The total amount paid
     * @param float $fee The transaction fee applied by the gateway
     * @param string|null $redirectUrl Optional URL to redirect the user's browser to
     */
    public function __construct(
        bool $isValid,
        bool $isSuccess = false,
        string $orderNumber = '',
        ?string $gatewayReference = null,
        float $amount = 0.0,
        float $fee = 0.0,
        ?string $redirectUrl = null
    ) {
        $this->isValid = $isValid;
        $this->isSuccess = $isSuccess;
        $this->orderNumber = $orderNumber;
        $this->gatewayReference = $gatewayReference;
        $this->amount = $amount;
        $this->fee = $fee;
        $this->redirectUrl = $redirectUrl;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getGatewayReference(): ?string
    {
        return $this->gatewayReference;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getFee(): float
    {
        return $this->fee;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}
