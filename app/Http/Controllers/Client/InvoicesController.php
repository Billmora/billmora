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
}
