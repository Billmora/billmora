<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
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
}
