@extends('admin::layouts.app')

@section('title', 'Automation Settings - Billing')

@section('body')
<form action="{{ route('admin.settings.automation.billing.update') }}" method="POST" class="flex flex-col gap-5">
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
            name="invoice_generation_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.invoice_generation_days_label') }}"
            helper="{{ __('admin/settings/automation.invoice_generation_days_helper') }}"
            value="{{ old('invoice_generation_days', Billmora::getAutomation('invoice_generation_days')) }}"
            required
        />
        <x-admin::input 
            name="invoice_reminder_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.invoice_reminder_days_label') }}"
            helper="{{ __('admin/settings/automation.invoice_reminder_days_helper') }}"
            value="{{ old('invoice_reminder_days', Billmora::getAutomation('invoice_reminder_days')) }}"
            required
        />
        <x-admin::input 
            name="invoice_overdue_first_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.invoice_overdue_first_days_label') }}"
            helper="{{ __('admin/settings/automation.invoice_overdue_first_days_helper') }}"
            value="{{ old('invoice_overdue_first_days', Billmora::getAutomation('invoice_overdue_first_days')) }}"
            required
        />
        <x-admin::input 
            name="invoice_overdue_second_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.invoice_overdue_second_days_label') }}"
            helper="{{ __('admin/settings/automation.invoice_overdue_second_days_helper') }}"
            value="{{ old('invoice_overdue_second_days', Billmora::getAutomation('invoice_overdue_second_days')) }}"
            required
        />
        <x-admin::input 
            name="invoice_overdue_third_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.invoice_overdue_third_days_label') }}"
            helper="{{ __('admin/settings/automation.invoice_overdue_third_days_helper') }}"
            value="{{ old('invoice_overdue_third_days', Billmora::getAutomation('invoice_overdue_third_days')) }}"
            required
        />
        <x-admin::input 
            name="invoice_auto_cancel_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.invoice_auto_cancel_days_label') }}"
            helper="{{ __('admin/settings/automation.invoice_auto_cancel_days_helper') }}"
            value="{{ old('invoice_auto_cancel_days', Billmora::getAutomation('invoice_auto_cancel_days')) }}"
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
