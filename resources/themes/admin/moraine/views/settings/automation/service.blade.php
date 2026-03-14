@extends('admin::layouts.app')

@section('title', 'Automation Settings - Service')

@section('body')
<form action="{{ route('admin.settings.automation.service.update') }}" method="POST" class="flex flex-col gap-5">
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
            name="service_suspend_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.service_suspend_days_label') }}"
            helper="{{ __('admin/settings/automation.service_suspend_days_helper') }}"
            value="{{ old('service_suspend_days', Billmora::getAutomation('service_suspend_days')) }}"
            required
        />
        <x-admin::input 
            name="service_terminate_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.service_terminate_days_label') }}"
            helper="{{ __('admin/settings/automation.service_terminate_days_helper') }}"
            value="{{ old('service_terminate_days', Billmora::getAutomation('service_terminate_days')) }}"
            required
        />
        <x-admin::toggle 
            name="auto_accept_cancellation"
            label="{{ __('admin/settings/automation.auto_accept_cancellation_label') }}"
            helper="{{ __('admin/settings/automation.auto_accept_cancellation_helper') }}"
            checked="{{ old('auto_accept_cancellation', Billmora::getAutomation('auto_accept_cancellation')) }}"
        />
    </div>
    @can('settings.automation.update')
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
