<?php

namespace App\Http\Controllers\Client;

use App\Contracts\GatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Plugin;
use App\Services\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Str;

class PaymentController extends Controller
{
    /**
     * Process payment for the specified invoice using the selected gateway plugin.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Invoice $invoice
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function process(Request $request, Invoice $invoice, PluginManager $pluginManager)
    {
        if ($invoice->status !== 'unpaid') {
            return redirect()->route('client.invoices.show', $invoice->invoice_number)
                ->with('error', __('client/invoices.payment.already_processed'));
        }

        $pluginId = $request->input('payment_method', $invoice->plugin_id);

        if (!$pluginId) {
            return redirect()->route('client.invoices.show', $invoice->invoice_number)
                ->with('error', __('client/invoices.payment.method_required'));
        }

        if ($request->has('payment_method')) {
            $request->validate([
                'payment_method' => [
                    'required', 
                    Rule::exists('plugins', 'id')->where(fn ($query) => $query->where('type', 'gateway')->where('is_active', true))
                ],
            ]);
            
            $invoice->update(['plugin_id' => $pluginId]);
        }

        $plugin = Plugin::find($pluginId);
        $gateway = $pluginManager->bootInstance($plugin);

        if (!$gateway instanceof GatewayInterface) {
            return redirect()->route('client.invoices.show', $invoice->invoice_number)
                ->with('error', __('client/invoices.payment.invalid_method'));
        }

        $invoice->load(['user.billing', 'items', 'order.package.catalog']);
    
        $options = [
            'description' => Str::limit("{$invoice->order->package->name} - {$invoice->order->package->catalog->name}", 200),
            'user' => $invoice->user->toArray(),
            'items' => $invoice->items->toArray(),
            'return_url' => route('client.invoices.show', $invoice->invoice_number),
        ];

        $response = $gateway->pay($invoice->invoice_number, (float) $invoice->total, $invoice->currency, $options);

        if (isset($response['success']) && $response['success'] === true) {
            if ($response['type'] === 'redirect') {
                return redirect()->away($response['data']); 
            }
        }

        return redirect()->route('client.invoices.show', $invoice->invoice_number)->with('error', $response['message']);
    }
}
