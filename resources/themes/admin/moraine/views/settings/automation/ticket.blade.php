@extends('admin::layouts.app')

@section('title', 'Automation Settings - Ticket')

@section('body')
<form action="{{ route('admin.settings.automation.ticket.update') }}" method="POST" class="flex flex-col gap-5">
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
            name="ticket_close_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.ticket_close_days_label') }}"
            helper="{{ __('admin/settings/automation.ticket_close_days_helper') }}"
            value="{{ old('ticket_close_days', Billmora::getAutomation('ticket_close_days')) }}"
            required
        />
        <x-admin::input 
            name="prune_ticket_attachments_days"
            type="number"
            min="0"
            label="{{ __('admin/settings/automation.prune_ticket_attachments_days_label') }}"
            helper="{{ __('admin/settings/automation.prune_ticket_attachments_days_helper') }}"
            value="{{ old('prune_ticket_attachments_days', Billmora::getAutomation('prune_ticket_attachments_days')) }}"
            required
        />
    </div>
    @can('settings.automation.update')
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
