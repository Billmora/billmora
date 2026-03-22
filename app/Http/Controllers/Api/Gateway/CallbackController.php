<?php

namespace App\Http\Controllers\Api\Gateway;

use App\Contracts\GatewayInterface;
use App\Events\PaymentCaptured;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Plugin;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    use AuditsSystem;

    /**
     * Handle incoming strictly API webhook callback.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Plugin $plugin
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request, Plugin $plugin, PluginManager $pluginManager)
    {
        $this->recordSystem('gateway.webhook.received', [
            'plugin' => $plugin->name,
            'payload' => $request->all(),
            'query' => $request->query()
        ], 'gateway');

        if (!$plugin->is_active || $plugin->type !== 'gateway') {
            return response()->json(['error' => 'Gateway instance not found or inactive.'], 404);
        }

        $gateway = $pluginManager->bootInstance($plugin);

        if (!$gateway instanceof GatewayInterface) {
            return response()->json(['error' => 'Invalid gateway implementation.'], 500);
        }

        try {
            $response = $gateway->webhook($request);

            if (!$response->isValid()) {
                return response()->json(['error' => 'Invalid signature or webhook processing failed.'], 400);
            }

            $invoice = Invoice::where('invoice_number', $response->getOrderNumber())->first();

            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found.'], 404);
            }

            if ($response->isSuccess() === true) {
                event(new PaymentCaptured($invoice, $plugin, $response));
                return response()->json(['message' => 'Payment processed successfully'], 200);
            }

            return response()->json(['message' => 'Webhook received but payment not successful.'], 200);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => 'Internal server error.'], 500);
        }
    }

    /**
     * Handle user returning from browser.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Plugin $plugin
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleReturn(Request $request, Plugin $plugin, PluginManager $pluginManager)
    {
        $this->recordSystem('gateway.return.received', [
            'plugin' => $plugin->name,
            'payload' => $request->all(),
            'query' => $request->query()
        ], 'gateway');

        $fallbackRedirect = url('/dashboard');

        if (!$plugin->is_active || $plugin->type !== 'gateway') {
            return redirect($fallbackRedirect)->with('error', 'Gateway instance not found or inactive.');
        }

        $gateway = $pluginManager->bootInstance($plugin);

        if (!$gateway instanceof GatewayInterface) {
            return redirect($fallbackRedirect)->with('error', 'Invalid gateway implementation.');
        }

        try {
            $response = $gateway->return($request);

            $redirectUrl = $response->getRedirectUrl() ?: $fallbackRedirect;

            if (!$response->isValid()) {
                return redirect($redirectUrl)->with('error', 'Invalid return signature or processing failed.');
            }

            $invoice = Invoice::where('invoice_number', $response->getOrderNumber())->first();

            if (!$invoice) {
                return redirect($redirectUrl)->with('error', 'Invoice not found.');
            }

            if ($response->isSuccess() === true) {
                event(new PaymentCaptured($invoice, $plugin, $response));
                return redirect($redirectUrl)->with('success', 'Payment processed successfully');
            }

            return redirect($redirectUrl)->with('error', 'Payment return received but not marked as successful.');
        } catch (\Exception $e) {
            report($e);
            return redirect($fallbackRedirect)->with('error', 'Internal server error processing return.');
        }
    }
}
