<?php

namespace Plugins\Gateways\PayPal;

use App\Contracts\GatewayInterface;
use App\Support\AbstractPlugin;
use App\Support\GatewayCallbackResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayPalGateway extends AbstractPlugin implements GatewayInterface
{
    /*
    |--------------------------------------------------------------------------
    | Plugin Contract
    |--------------------------------------------------------------------------
    */

    public function getConfigSchema(): array
    {
        return [
            'client_id' => [
                'label' => 'Client ID',
                'type'  => 'text',
                'rules' => 'required|string',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'type'  => 'password',
                'rules' => 'required|string',
            ],
            'mode' => [
                'label'   => 'Environment',
                'type'    => 'select',
                'options' => [
                    'sandbox' => 'Sandbox',
                    'live'    => 'Live',
                ],
                'rules'   => 'required|in:sandbox,live',
                'default' => 'sandbox',
            ],
        ];
    }

    public function getPermissions(): array
    {
        return ['gateways.paypal'];
    }

    /*
    |--------------------------------------------------------------------------
    | Gateway Contract
    |--------------------------------------------------------------------------
    */

    public function isApplicable(float $amount, string $currency): bool
    {
        $unsupported = [
            'IDR', 'VND', 'TWD', 'PKR', 'BDT', 'MMK',
        ];

        return ! in_array(strtoupper($currency), $unsupported, true);
    }

    public function pay(string $invoiceNumber, float $amount, string $currency, array $options = []): mixed
    {
        try {
            $accessToken = $this->getAccessToken();

            $callbackUrl = route('client.gateways.return', [
                'plugin' => $this->getPluginModel()->id,
            ]);

            $returnUrl = $callbackUrl . '?invoice=' . urlencode($invoiceNumber);
            $cancelUrl = $options['return_url'] ?? route('client.invoices.show', $invoiceNumber);

            $payload = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $invoiceNumber,
                        'custom_id'    => $invoiceNumber,
                        'description'  => $options['description'] ?? "Payment for Invoice {$invoiceNumber}",
                        'amount'       => [
                            'currency_code' => strtoupper($currency),
                            'value'         => number_format($amount, 2, '.', ''),
                        ],
                    ],
                ],
                'application_context' => [
                    'brand_name'          => config('app.name'),
                    'user_action'         => 'PAY_NOW',
                    'shipping_preference' => 'NO_SHIPPING',
                    'return_url'          => $returnUrl,
                    'cancel_url'          => $cancelUrl,
                ],
            ];

            $response = $this->request('POST', '/v2/checkout/orders', $accessToken, $payload);

            $approvalUrl = collect($response['links'] ?? [])
                ->firstWhere('rel', 'approve')['href'] ?? null;

            if (! $approvalUrl) {
                return [
                    'success' => false,
                    'message' => 'Unable to retrieve PayPal approval URL.',
                ];
            }

            return [
                'success' => true,
                'type'    => 'redirect',
                'data'    => $approvalUrl,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function webhook(Request $request): GatewayCallbackResponse
    {
        return new GatewayCallbackResponse(
            isValid: false,
            isSuccess: false,
            orderNumber: ''
        );
    }

    public function return(Request $request): GatewayCallbackResponse
    {
        $paypalOrderId = $request->query('token');
        $invoiceNumber = $request->query('invoice');

        if (! $paypalOrderId || ! $invoiceNumber) {
            return new GatewayCallbackResponse(
                isValid: false,
                isSuccess: false,
                orderNumber: (string) $invoiceNumber,
                redirectUrl: $invoiceNumber ? route('client.invoices.show', $invoiceNumber) : url('/')
            );
        }

        try {
            $accessToken = $this->getAccessToken();

            $capture = $this->request(
                'POST',
                "/v2/checkout/orders/{$paypalOrderId}/capture",
                $accessToken,
                ['payment_source' => new \stdClass()]
            );

            if (($capture['status'] ?? '') !== 'COMPLETED') {
                return new GatewayCallbackResponse(
                    isValid: true,
                    isSuccess: false,
                    orderNumber: $invoiceNumber,
                    redirectUrl: route('client.invoices.show', $invoiceNumber)
                );
            }

            $captureUnit = $capture['purchase_units'][0]['payments']['captures'][0] ?? [];

            return new GatewayCallbackResponse(
                isValid: true,
                isSuccess: true,
                orderNumber: $invoiceNumber,
                gatewayReference: $captureUnit['id'] ?? $paypalOrderId,
                amount: (float) ($captureUnit['amount']['value'] ?? 0),
                fee: 0,
                redirectUrl: route('client.invoices.show', $invoiceNumber)
            );

        } catch (\Exception $e) {
            return new GatewayCallbackResponse(
                isValid: false,
                isSuccess: false,
                orderNumber: (string) $invoiceNumber,
                redirectUrl: $invoiceNumber ? route('client.invoices.show', $invoiceNumber) : url('/')
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth(
            $this->getInstanceConfig('client_id'),
            $this->getInstanceConfig('client_secret')
        )
        ->asForm()
        ->post($this->baseUrl() . '/v1/oauth2/token', [
            'grant_type' => 'client_credentials',
        ]);

        if (! $response->successful()) {
            throw new \Exception('PayPal authentication failed: ' . $response->status());
        }

        return $response->json('access_token');
    }

    private function request(string $method, string $endpoint, string $accessToken, array $payload): array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->asJson()
            ->{strtolower($method)}($this->baseUrl() . $endpoint, $payload);

        if (! $response->successful()) {
            $error = $response->json('message') ?? $response->body();

            throw new \Exception("PayPal API error ({$response->status()}): {$error}");
        }

        return $response->json() ?? [];
    }

    private function baseUrl(): string
    {
        return $this->getInstanceConfig('mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }
}
