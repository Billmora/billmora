<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Service;
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

        $activeServices = Service::where('user_id', $user->id)
            ->active()
            ->with(['package.catalog'])
            ->select('id', 'name', 'package_id', 'next_due_date')
            ->paginate(10);

        return view('client::index',  compact([
            'user',
            'activeServicesCount',
            'unpaidInvoicesCount',
            'activeServices'
        ]));
    }
}
