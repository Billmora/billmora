<?php

namespace Plugins\Gateways\Duitku;

use App\Support\AbstractPlugin;
use App\Support\GatewayCallbackResponse;
use App\Contracts\GatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DuitkuGateway extends AbstractPlugin implements GatewayInterface
{
    public function getConfigSchema(): array
    {
        return [
            'merchant_code' => [
                'type' => 'text',
                'label' => 'Merchant Code',
                'rules' => 'required|string'
            ],
            'api_key'  => [
                'type' => 'password',
                'label' => 'API Key',
                'rules' => 'required|string'
            ],
            'environment'  => [
                'type' => 'select',
                'label' => 'Environment',
                'options' => [
                    'sandbox' => 'Sandbox',
                    'production' => 'Production'],
                    'rules' => 'required|in:sandbox,production'
            ],
        ];
    }

    public function isApplicable(float $amount, string $currency): bool
    {
        // IDR must be higher than 10K
        if ($currency === 'IDR' && $amount < 10000) {
            return false;
        }

        // Only supported IDR
        if ($currency !== 'IDR') return false;

        return true;
    }

    public function pay(string $invoiceNumber, float $amount, string $currency, array $options = []): mixed
    {
        $merchantCode = $this->getInstanceConfig('merchant_code');
        $apiKey = $this->getInstanceConfig('api_key');
        $environment = $this->getInstanceConfig('environment', 'sandbox');

        $baseUrl = $environment === 'production' 
            ? 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry' 
            : 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';

        $paymentAmount = (int) $amount; 
        
        $signature = md5($merchantCode . $invoiceNumber . $paymentAmount . $apiKey);

        $originalItemsTotal = collect($options['items'] ?? [])->sum(function ($item) {
            return (int) round((float) $item['unit_price']) * (int) $item['quantity'];
        });

        // If partially paid (e.g. credit balance applied), send a single summary line to avoid Duitku validation errors
        if ($originalItemsTotal !== $paymentAmount) {
            $itemDetails = [
                [
                    'name'     => Str::limit('Remaining Payment for Invoice ' . $invoiceNumber, 50),
                    'price'    => $paymentAmount,
                    'quantity' => 1,
                ]
            ];
        } else {
            // Full payment — send original item details
            $itemDetails = collect($options['items'] ?? [])->flatMap(function ($item) {
                $price = (int) round((float) $item['unit_price']);
                $quantity = (int) $item['quantity'];

                return array_fill(0, $quantity, [
                    'name'     => Str::limit($item['description'], 50),
                    'price'    => $price,
                    'quantity' => 1,
                ]);
            })->values()->toArray();
        }

        $payload = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $paymentAmount,
            'merchantOrderId' => $invoiceNumber,
            'productDetails' => $options['description'],
            'itemDetails' => $itemDetails,
            'email' => $options['user']['email'],
            'paymentMethod'  => 'VC',
            'customerVaName' => $options['user']['fullname'],
            'phoneNumber' => $options['user']['billing']['phone_number'],
            'returnUrl' => $options['return_url'],
            'callbackUrl' => route('api.gateways.webhook', ['plugin' => $this->getPluginModel()->id]), 
            'signature' => $signature,
            'expiryPeriod' => 10,
        ];

        try {
            $response = Http::post($baseUrl, $payload);
            $result = $response->json();

            if ($response->successful() && isset($result['statusCode']) && $result['statusCode'] === '00') {
                return [
                    'success' => true,
                    'type' => 'redirect',
                    'data' => $result['paymentUrl'],
                    'message' => 'Success',
                ];
            }

            return [
                'success' => false,
                'message' => 'API Duitku Error: ' . ($result['Message']),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed connect to Duitku: ' . $e->getMessage(),
            ];
        }
    }

    public function webhook(Request $request): GatewayCallbackResponse
    {
        $merchantCode = $this->getInstanceConfig('merchant_code');
        $apiKey = $this->getInstanceConfig('api_key');

        $reqMerchantOrderId = $request->input('merchantOrderId');
        $reqSignature = $request->input('signature');
        $reqAmount = $request->input('amount');
        $reqFee = $request->input('fee', 0);
        $resultCode = $request->input('resultCode');
        $reference = $request->input('reference');
        
        $calcSignature = md5($merchantCode . $reqAmount . $reqMerchantOrderId . $apiKey);
        $isValid = ($reqSignature === $calcSignature);
        $isSuccess = ($resultCode === '00');

        return new GatewayCallbackResponse(
            isValid: $isValid,
            isSuccess: $isSuccess,
            orderNumber: (string) $reqMerchantOrderId,
            gatewayReference: (string) $reference,
            amount: (float) $reqAmount,
            fee: (float) $reqFee
        );
    }

    public function return(Request $request): GatewayCallbackResponse
    {
        return new GatewayCallbackResponse(
            isValid: false,
            isSuccess: false,
            orderNumber: ''
        );
    }
}