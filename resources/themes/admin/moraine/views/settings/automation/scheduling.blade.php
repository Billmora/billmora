@extends('admin::layouts.app')

@section('title', 'Automation Settings - Scheduling')

@section('body')
<form action="{{ route('admin.settings.automation.scheduling.update') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PATCH')
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.automation.scheduling'),
                'icon' => 'lucide-calendar-check',
                'label' => __('admin/settings/automation.tabs.scheduling'),
            ],
            [
                'route' => route('admin.settings.automation.billing'),
                'icon' => 'lucide-receipt-text',
                'label' => __('admin/settings/automation.tabs.billing'),
            ],
            [
                'route' => route('admin.settings.automation.service'),
                'icon' => 'lucide-scan-text',
                'label' => __('admin/settings/automation.tabs.service'),
            ],
            [
                'route' => route('admin.settings.automation.ticket'),
                'icon' => 'lucide-ticket',
                'label' => __('admin/settings/automation.tabs.ticket'),
            ],
        ]" 
        active="{{ request()->url() }}" />
    <div class="grid grid-cols-1 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="time_of_day"
            type="time"
            label="{{ __('admin/settings/automation.time_of_day_label') }}"
            helper="{{ __('admin/settings/automation.time_of_day_helper') }}"
            value="{{ old('time_of_day', Billmora::getAutomation('time_of_day')) }}"
            required
        />
        <x-admin::input 
            name="prune_email_history_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.prune_email_history_days_label') }}"
            helper="{{ __('admin/settings/automation.prune_email_history_days_helper') }}"
            value="{{ old('prune_email_history_days', Billmora::getAutomation('prune_email_history_days')) }}"
            required
        />
        <x-admin::input 
            name="prune_system_logs_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.prune_system_logs_days_label') }}"
            helper="{{ __('admin/settings/automation.prune_system_logs_days_helper') }}"
            value="{{ old('prune_system_logs_days', Billmora::getAutomation('prune_system_logs_days')) }}"
            required
        />
    </div>
    @can('settings.automation.update')
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
