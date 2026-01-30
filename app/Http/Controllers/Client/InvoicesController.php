<?php

namespace App\Http\Controllers\Client;

use App\Facades\Billmora;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
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

        $invoices = Invoice::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['order.service.package.catalog'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client::invoices.index', compact('invoices'));
    }

    /**
     * Display the specified invoice with related data.
     *
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\View\View
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function show(Invoice $invoice)
    {
        $user = Auth::user();

        $invoice->loadMissing(['order.service.package.catalog', 'order.user']);

        if ($invoice->order->user_id !== $user->id) {
            abort(403);
        }

        return view('client::invoices.show', compact('invoice'));
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
        if (!Billmora::getGeneral('invoice_pdf')) {
            abort(404);
        }

        if (Auth::id() !== $invoice->user_id && !Auth::user()->isAdmin) {
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
