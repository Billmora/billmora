@extends('admin::layouts.app')

@section('title', 'Admin Dashboard')

@section('body')
<div class="grid gap-5">
    <h2 class="text-2xl font-bold text-slate-600">{{ __('admin/dashboard.welcome', ['name' => Auth::user()->first_name]) }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-lg p-6 border-b-4 border-green-500">
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ __('admin/dashboard.revenue_this_month') }}</p>
            <h3 class="text-3xl font-bold text-slate-600 mt-2">{{ $formattedRevenue }}</h3>
        </div>
        <div class="bg-white rounded-lg p-6 border-b-4 border-red-500">
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ __('admin/dashboard.unpaid_invoices') }}</p>
            <div class="flex items-baseline mt-2">
                <h3 class="text-3xl font-bold text-slate-600">{{ $unpaidInvoicesCount }}</h3>
                <span class="ml-2 text-sm text-red-500 font-semibold">{{ __('admin/dashboard.overdue_badge', ['count' => $overdueInvoicesCount]) }}</span>
            </div>
        </div>
        <div class="bg-white rounded-lg p-6 border-b-4 border-blue-500">
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ __('admin/dashboard.active_services') }}</p>
            <div class="flex items-baseline mt-2">
                <h3 class="text-3xl font-bold text-slate-600">{{ $activeServices }}</h3>
                <span class="ml-2 text-sm text-orange-500 font-semibold">{{ __('admin/dashboard.suspended_badge', ['count' => $suspendedServices]) }}</span>
            </div>
        </div>
        <div class="bg-white rounded-lg p-6 border-b-4 border-purple-500">
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ __('admin/dashboard.pending_tickets') }}</p>
            <h3 class="text-3xl font-bold text-slate-600 mt-2">{{ $pendingTickets }}</h3>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="h-fit bg-white border-2 border-billmora-2 rounded-2xl p-6 lg:col-span-2">
            <h3 class="text-lg font-semibold text-slate-600 mb-4">{{ __('admin/dashboard.revenue_overview', ['year' => now()->year]) }}</h3>
            <div class="relative h-80 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="flex flex-col gap-6 lg:col-span-1">
            <div class="bg-white border-2 border-billmora-2 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-slate-600 mb-4">{{ __('admin/dashboard.billing_summary') }}</h3>
                <ul>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm font-medium uppercase">{{ __('admin/dashboard.billing_today') }}</span>
                        <span class="text-slate-600 font-bold">{{ $billingSummary['today'] }}</span>
                    </li>
                    <hr class="border-t-2 border-billmora-2 my-3">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm font-medium uppercase">{{ __('admin/dashboard.billing_month') }}</span>
                        <span class="text-slate-600 font-bold">{{ $billingSummary['month'] }}</span>
                    </li>
                    <hr class="border-t-2 border-billmora-2 my-3">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm font-medium uppercase">{{ __('admin/dashboard.billing_year') }}</span>
                        <span class="text-slate-600 font-bold">{{ $billingSummary['year'] }}</span>
                    </li>
                    <hr class="border-t-2 border-billmora-2 my-3">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-500 font-semibold uppercase">{{ __('admin/dashboard.billing_all_time') }}</span>
                        <span class="text-billmora-primary font-bold text-lg">{{ $billingSummary['all_time'] }}</span>
                    </li>
                </ul>
            </div>
            <div class="bg-white border-2 border-billmora-2 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-slate-600 mb-4">{{ __('admin/dashboard.system_summary') }}</h3>
                <ul>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">{{ __('admin/dashboard.total_clients') }}</span>
                        <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-full text-xs font-semibold">{{ $totalClients }}</span>
                    </li>
                    <hr class="border-t-2 border-billmora-2 my-3">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">{{ __('admin/dashboard.tasks') }}</span>
                        <a href="{{ route('admin.tasks') }}" class="bg-red-100 text-red-700 hover:bg-red-200 py-1 px-3 rounded-full text-xs font-semibold transition">
                            {{ __('admin/dashboard.view_queue') }}
                        </a>
                    </li>
                    <hr class="border-t-2 border-billmora-2 my-3">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">{{ __('admin/dashboard.automations') }}</span>
                        <a href="{{ route('admin.automations') }}" class="bg-green-100 text-green-700 hover:bg-green-200 py-1 px-3 rounded-full text-xs font-semibold transition">
                            {{ __('admin/dashboard.check_health') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartDatasetLabel = @json(__('admin/dashboard.chart_dataset_label'));

    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        const dataValues = @json($monthlyRevenueData);
        const labels = @json($monthsLabel);

        const currencyPrefix = "{{ $currencyActive->prefix ?? '' }}";
        const currencySuffix = "{{ $currencyActive->suffix ? ' ' . $currencyActive->suffix : '' }}";

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: chartDatasetLabel,
                    data: dataValues,
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(16, 185, 129, 1)',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    let formattedNumber = new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(context.parsed.y);
                                    label += currencyPrefix + formattedNumber + currencySuffix;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return currencyPrefix + (value / 1000000).toFixed(1) + 'M';
                                } else if (value >= 1000) {
                                    return currencyPrefix + (value / 1000).toFixed(1) + 'K';
                                }
                                return currencyPrefix + value;
                            }
                        },
                        grid: { borderDash: [5, 5] }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush