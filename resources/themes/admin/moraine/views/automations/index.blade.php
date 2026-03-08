@extends('admin::layouts.app')

@section('title', 'Automation Status')

@section('body')
<div class="grid gap-5">
    <h2 class="text-2xl font-bold text-slate-600">Automation Status</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                    <x-lucide-clock class="w-6 h-6" />
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold text-slate-500">{{ __('admin/automations.scheduled_time') }}</p>
                    <p class="text-lg font-semibold text-slate-600">{{ $scheduledTime }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                    <x-lucide-check class="w-6 h-6" />
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold text-slate-500">{{ __('admin/automations.last_run') }}</p>
                    <p class="text-lg font-semibold text-slate-600">{{ $lastRun->format(Billmora::getGeneral('company_date_format') . ' H:i') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                    <x-lucide-zap class="w-6 h-6" />
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold text-slate-500">{{ __('admin/automations.next_run') }}</p>
                    <p class="text-lg font-semibold text-slate-600">{{ $nextRun->format(Billmora::getGeneral('company_date_format') . ' H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1">
        <div class="bg-white border-2 border-billmora-2 rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-slate-600 mb-4">{{ __('admin/automations.monthly_overview') }}</h3>
            <div class="relative h-80 w-full">
                <canvas id="monthlyAutomationChart"></canvas>
            </div>
        </div>
    </div>
    <h3 class="text-lg font-semibold text-slate-600">{{ __('admin/automations.todays_executions') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white border-2 border-billmora-2 rounded-2xl p-5 flex justify-between items-center">
            <div>
                <p class="text-sm text-slate-500 font-semibold uppercase">{{ __('admin/automations.invoices_generated') }}</p>
                <h4 class="text-2xl font-bold text-slate-600 mt-1">{{ $stats['invoices_generated_today'] ?? 0 }}</h4>
            </div>
        </div>
        <div class="bg-white border-2 border-billmora-2 rounded-2xl p-5 flex justify-between items-center">
            <div>
                <p class="text-sm text-slate-500 font-semibold uppercase">{{ __('admin/automations.reminders_sent') }}</p>
                <h4 class="text-2xl font-bold text-slate-600 mt-1">{{ $stats['reminders_sent_today'] ?? 0 }}</h4>
            </div>
        </div>
        <div class="bg-white border-2 border-billmora-2 rounded-2xl p-5 flex justify-between items-center">
            <div>
                <p class="text-sm text-slate-500 font-semibold uppercase">{{ __('admin/automations.tickets_auto_closed') }}</p>
                <h4 class="text-2xl font-bold text-slate-600 mt-1">{{ $stats['tickets_closed_today'] ?? 0 }}</h4>
            </div>
        </div>
        <div class="bg-white border-2 border-billmora-2 rounded-2xl p-5 flex justify-between items-center">
            <div>
                <p class="text-sm text-slate-500 font-semibold uppercase">{{ __('admin/automations.services_suspended') }}</p>
                <h4 class="text-2xl font-bold text-orange-500 mt-1">{{ $stats['services_suspended_today'] ?? 0 }}</h4>
            </div>
        </div>
        <div class="bg-white border-2 border-billmora-2 rounded-2xl p-5 flex justify-between items-center">
            <div>
                <p class="text-sm text-slate-500 font-semibold uppercase">{{ __('admin/automations.services_terminated') }}</p>
                <h4 class="text-2xl font-bold text-red-500 mt-1">{{ $stats['services_terminated_today'] ?? 0 }}</h4>
            </div>
        </div>
        <div class="bg-white border-2 border-billmora-2 rounded-2xl p-5 flex justify-between items-center">
            <div>
                <p class="text-sm text-slate-500 font-semibold uppercase">{{ __('admin/automations.cancellations_processed') }}</p>
                <h4 class="text-2xl font-bold text-slate-600 mt-1">{{ $stats['cancellations_processed_today'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    @php
        $chartLabels = [
            __('admin/automations.invoices_generated'),
            __('admin/automations.reminders_sent'),
            __('admin/automations.services_suspended'),
            __('admin/automations.services_terminated'),
            __('admin/automations.cancellations_processed'),
            __('admin/automations.chart_label_tickets'),
        ];
    @endphp

    const chartLabels = @json($chartLabels);
    const chartDatasetLabel = @json(__('admin/automations.chart_dataset_label'));

    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('monthlyAutomationChart').getContext('2d');
        
        const data = [
            {{ $stats['invoices_generated_month'] ?? 0 }},
            {{ $stats['reminders_sent_month'] ?? 0 }},
            {{ $stats['services_suspended_month'] ?? 0 }},
            {{ $stats['services_terminated_month'] ?? 0 }},
            {{ $stats['cancellations_processed_month'] ?? 0 }},
            {{ $stats['tickets_closed_month'] ?? 0 }}
        ];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: chartDatasetLabel,
                    data: data,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(107, 114, 128, 0.7)',
                        'rgba(139, 92, 246, 0.7)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(107, 114, 128, 1)',
                        'rgba(139, 92, 246, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            borderDash: [5, 5]
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endpush