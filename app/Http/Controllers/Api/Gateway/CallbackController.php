<?php

namespace App\Http\Controllers\Api\Gateway;

use App\Contracts\GatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Plugin;
use App\Models\Transaction;
use App\Services\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CallbackController extends Controller
{

    /**
     * Handle incoming payment gateway callback and process invoice payment.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $provider
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, string $provider, PluginManager $pluginManager)
    {
        $plugin = Plugin::where('type', 'gateway')
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();

        if (!$plugin) {
            return response()->json(['error' => 'Gateway not found.'], 404);
        }

        $gateway = $pluginManager->bootInstance($plugin);

        if (!$gateway instanceof GatewayInterface) {
            return response()->json(['error' => 'Invalid gateway implementation.'], 500);
        }

        try {
            $response = $gateway->callback($request);

            if (empty($response['is_valid']) || $response['is_valid'] !== true) {
                return response()->json(['error' => 'Invalid signature.'], 400);
            }

            return DB::transaction(function () use ($response, $plugin) {
                
                $invoice = Invoice::where('invoice_number', $response['order_number'])->lockForUpdate()->first();

                if (!$invoice) {
                    return response()->json(['error' => 'Invoice not found.'], 404);
                }

                if ($invoice->status === 'paid') {
                    return response()->json(['message' => 'Invoice already paid.'], 200);
                }

                if ($response['is_success'] === true) {
                    
                    $invoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    Transaction::create([
                        'user_id' => $invoice->user_id,
                        'invoice_id' => $invoice->id,
                        'plugin_id' => $plugin->id,
                        'reference' => $response['gateway_reference'],
                        'description' => "Payment of Invoice {$invoice->invoice_number} via {$plugin->name}",
                        'currency' => $invoice->currency,
                        'amount' => $response['amount'],
                        'fee' => $response['fee'] ?? 0,
                    ]);

                    // TODO: Activated service with provisioning

                    return response()->json(['message' => 'Payment processed successfully'], 200);
                }

                return response()->json(['message' => 'Webhook received but payment not successful.'], 200);
            });

        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error.'], 500);
        }
    }
}
