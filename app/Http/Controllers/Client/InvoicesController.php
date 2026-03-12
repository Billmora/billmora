<?php

namespace App\Http\Controllers\Client;

use Billmora;
use App\Contracts\GatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Plugin;
use App\Services\PluginManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicesController extends Controller
{
    /**
     * Display a paginated list of user's invoices.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        $invoices = Invoice::where('user_id', $user->id)
            ->with(['order.items'])
            ->orderBy('created_at', 'desc')
            ->paginate(Billmora::getGeneral('misc_client_pagination'));

        return view('client::invoices.index', compact('invoices'));
    }

    /**
     * Display the specified invoice with related data.
     *
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\View\View
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function show(Invoice $invoice, PluginManager $pluginManager)
    {
        $user = Auth::user();

        $invoice->loadMissing(['order.items', 'order.user', 'transactions']);

        if ($user->id !== $invoice->user_id) {
            abort(403);
        }

        $creditBalance = $user->getCreditWallet($invoice->currency)->balance;

        $activeGateways = Plugin::where('type', 'gateway')->where('is_active', true)->get();
        $gateways = collect();

        foreach ($activeGateways as $gatewayRecord) {
            $instance = $pluginManager->bootInstance($gatewayRecord);
            
            if ($instance instanceof GatewayInterface) {
                if ($instance->isApplicable((float) $invoice->amount_due, $invoice->currency)) {
                    $gateways->push($gatewayRecord);
                }
            }
        }

        return view('client::invoices.show', compact('invoice', 'gateways', 'creditBalance'));
    }

    /**
     * Download the invoice as a PDF file.
     *
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function download(Invoice $invoice)
    {
        $user = Auth::user();

        if (!Billmora::getGeneral('invoice_pdf')) {
            abort(404);
        }

        if ($user->id !== $invoice->user_id) {
            abort(403);
        }

        $paperSize = Billmora::getGeneral('invoice_pdf_size');

        $pdf = Pdf::loadView('invoice::index', [
            'invoice' => $invoice->load([
                'user',
                'order',
                'items',
                'items.service',
            ]),
        ])
        ->setPaper($paperSize, 'portrait')
        ->setOption('enable-local-file-access', true)
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

        $filename = "Invoice-{$invoice->invoice_number}.pdf";
        
        return $pdf->download($filename);
    }
}
