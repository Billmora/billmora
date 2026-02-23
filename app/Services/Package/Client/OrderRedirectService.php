<?php

namespace App\Services\Package\Client;

use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Support\Facades\Session;
use Billmora;

class OrderRedirectService
{
    /**
     * Handle redirect after successful order creation based on settings.
     *
     * @param \App\Models\Order $order
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Order $order, Invoice $invoice)
    {
        switch (Billmora::getGeneral('ordering_redirect')) {
            case 'complete':
                Session::put('completed_order_data', [
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                ]);
                return redirect()->route('client.checkout.complete');
                
            case 'invoice':
                return redirect()->route('client.invoices.show', $invoice->invoice_number);
                
            case 'payment':
                return redirect()->route('client.invoices.pay', $invoice->invoice_number);
            
            default:
                return redirect()->route('client.dashboard');
        }
    }
}