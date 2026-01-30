<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    /**
     * Display a paginated list of all invoices.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $invoices = Invoice::whereHas('order')
            ->with(['order.service.package.catalog'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin::invoices.index', compact('invoices'));
    }
}
