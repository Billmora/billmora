<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\CurrencyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with billing summaries, service stats, and monthly revenue chart data.
     *
     * @param  \App\Services\CurrencyService  $currencyService
     * @return \Illuminate\View\View
     */
    public function index(CurrencyService $currencyService)
    {
        $today = now();

        $activeCurrency = app(Currency::class);
        $activeRate = $activeCurrency->base_rate > 0 ? $activeCurrency->base_rate : 1;
        $currencies = View::shared('currencies')->keyBy('code');

        $revenueTodayBase = $this->calculateConvertedRevenue($today->copy()->startOfDay(), $today->copy()->endOfDay(), $currencies);
        $revenueMonthBase = $this->calculateConvertedRevenue($today->copy()->startOfMonth(), $today->copy()->endOfMonth(), $currencies);
        $revenueYearBase = $this->calculateConvertedRevenue($today->copy()->startOfYear(), $today->copy()->endOfYear(), $currencies);
        $revenueAllBase = $this->calculateConvertedRevenue(null, null, $currencies); // All time

        $billingSummary = [
            'today' => $currencyService->format($revenueTodayBase * $activeRate),
            'month' => $currencyService->format($revenueMonthBase * $activeRate),
            'year' => $currencyService->format($revenueYearBase * $activeRate),
            'all_time' => $currencyService->format($revenueAllBase * $activeRate),
        ];

        $formattedRevenue = $billingSummary['month'];

        $unpaidInvoicesCount = Invoice::where('status', 'unpaid')->count();
        $overdueInvoicesCount = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', $today->startOfDay())
            ->count();

        $activeServices = Service::where('status', 'active')->count();
        $suspendedServices = Service::where('status', 'suspended')->count();
        $totalClients = User::clients()->count();

        $pendingTickets = Ticket::whereIn('status', ['open', 'customer_reply'])->count();

        $yearlyInvoices = Invoice::where('status', 'paid')
            ->whereYear('paid_at', $today->year)
            ->select(DB::raw('MONTH(paid_at) as month'), 'currency', DB::raw('SUM(total) as total_amount'))
            ->groupBy('month', 'currency')
            ->get();

        $monthlyRevenueBase = array_fill(1, 12, 0);
        $monthsLabel = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthsLabel[] = Carbon::create($today->year, $i, 1)->format('M');
        }

        foreach ($yearlyInvoices as $inv) {
            $invCurrency = $currencies->get($inv->currency);
            $rate = $invCurrency && $invCurrency->base_rate > 0 ? $invCurrency->base_rate : 1;
            
            $monthlyRevenueBase[$inv->month] += ($inv->total_amount / $rate);
        }

        $monthlyRevenueData = [];
        foreach ($monthlyRevenueBase as $baseVal) {
            $monthlyRevenueData[] = round($baseVal * $activeRate, 2); 
        }

        return view('admin::index', compact(
            'formattedRevenue',
            'unpaidInvoicesCount',
            'overdueInvoicesCount',
            'activeServices',
            'suspendedServices',
            'totalClients',
            'pendingTickets',
            'monthlyRevenueData',
            'monthsLabel',
            'billingSummary'
        ));
    }

    /**
     * Calculate total converted revenue across all currencies within an optional date range.
     *
     * @param  \Carbon\Carbon|null  $startDate
     * @param  \Carbon\Carbon|null  $endDate
     * @param  \Illuminate\Support\Collection  $currencies
     * @return float
     */
    private function calculateConvertedRevenue(?Carbon $startDate, ?Carbon $endDate, $currencies): float
    {
        $query = Invoice::where('status', 'paid');

        if ($startDate && $endDate) {
            $query->whereBetween('paid_at', [$startDate, $endDate]);
        }

        $revenues = $query->select('currency', DB::raw('SUM(total) as total_amount'))
            ->groupBy('currency')
            ->get();

        $totalBase = 0;
        foreach ($revenues as $rev) {
            $invCurrency = $currencies->get($rev->currency);
            $rate = $invCurrency && $invCurrency->base_rate > 0 ? $invCurrency->base_rate : 1;
            $totalBase += ($rev->total_amount / $rate);
        }

        return $totalBase;
    }
}
