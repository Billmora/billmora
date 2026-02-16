<?php

namespace App\Contracts;

use App\Models\Invoice;
use Illuminate\Http\Request;

interface GatewayInterface extends PluginInterface
{
    /**
     * Process checkout and redirect to payment gateway.
     *
     * @param \App\Models\Invoice $invoice
     * @return mixed
     */
    public function checkout(Invoice $invoice);

    /**
     * Handle payment gateway callback and update invoice status.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function callback(Request $request);
}