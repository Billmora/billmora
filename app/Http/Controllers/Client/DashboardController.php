<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    /**
     * Display the client dashboard homepage.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        $activeServicesCount = Service::where('user_id', $user->id)
            ->active()
            ->count();

        $unpaidInvoicesCount = Invoice::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->unpaid()
            ->count();

        $openTicketsCount = Ticket::where('status', 'open')->count();

        $activeServices = Service::where('user_id', $user->id)
            ->active()
            ->with(['package.catalog'])
            ->limit(5)
            ->get();

        $unpaidInvoices = Invoice::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->unpaid()
            ->with(['items'])
            ->limit(5)
            ->get();

        $openTickets = Ticket::where('status', 'open')
            ->limit(5)
            ->get();

        return view('client::index',  compact([
            'user',
            'activeServicesCount',
            'unpaidInvoicesCount',
            'openTicketsCount',
            'activeServices',
            'unpaidInvoices',
            'openTickets',
        ]));
    }
}
