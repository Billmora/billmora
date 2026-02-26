@extends('admin::layouts.app')

@section('title', 'Notify Settings - Ticket')

@section('body')
<form action="{{ route('admin.settings.ticket.notify.update') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PATCH')
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.ticket.ticketing'),
                'icon' => 'lucide-tags',
                'label' => __('admin/settings/ticket.tabs.ticketing'),
            ],
            [
                'route' => route('admin.settings.ticket.piping'),
                'icon' => 'lucide-mailbox',
                'label' => __('admin/settings/ticket.tabs.piping'),
            ],
            [
                'route' => route('admin.settings.ticket.notify'),
                'icon' => 'lucide-send-horizontal',
                'label' => __('admin/settings/ticket.tabs.notify'),
            ],
        ]" 
        active="{{ request()->url() }}" />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::toggle 
            name="notify_client_on_open"
            label="{{ __('admin/settings/ticket.notify_client_on_open_label') }}"
            helper="{{ __('admin/settings/ticket.notify_client_on_open_helper') }}"
            checked="{{ old('notify_client_on_open', Billmora::getTicket('notify_client_on_open')) }}"
        />
        <x-admin::toggle 
            name="notify_client_on_staff_open"
            label="{{ __('admin/settings/ticket.notify_client_on_staff_open_label') }}"
            helper="{{ __('admin/settings/ticket.notify_client_on_staff_open_helper') }}"
            checked="{{ old('notify_client_on_staff_open', Billmora::getTicket('notify_client_on_staff_open')) }}"
        />
        <x-admin::toggle 
            name="notify_client_on_staff_answered"
            label="{{ __('admin/settings/ticket.notify_client_on_staff_answered_label') }}"
            helper="{{ __('admin/settings/ticket.notify_client_on_staff_answered_helper') }}"
            checked="{{ old('notify_client_on_staff_answered', Billmora::getTicket('notify_client_on_staff_answered')) }}"
        />
        <x-admin::toggle 
            name="notify_staff_on_client_reply"
            label="{{ __('admin/settings/ticket.notify_staff_on_client_reply_label') }}"
            helper="{{ __('admin/settings/ticket.notify_staff_on_client_reply_helper') }}"
            checked="{{ old('notify_staff_on_client_reply', Billmora::getTicket('notify_staff_on_client_reply')) }}"
        />
        <x-admin::select 
            name="notify_staff_fallback"
            label="{{ __('admin/settings/ticket.notify_staff_fallback_label') }}"
            helper="{!! __('admin/settings/ticket.notify_staff_fallback_helper') !!}"
            required
        >
            <option value="none" {{ old('notify_staff_fallback', Billmora::getTicket('notify_staff_fallback')) === 'none' ? 'selected' : '' }}>None</option>
            <option value="departement" {{ old('notify_staff_fallback', Billmora::getTicket('notify_staff_fallback')) === 'departement' ? 'selected' : '' }}>Departement</option>
            <option value="assigned" {{ old('notify_staff_fallback', Billmora::getTicket('notify_staff_fallback')) === 'assigned' ? 'selected' : '' }}>Assigned</option>
        </x-admin::select>
    </div>
    @can('settings.ticket.update')
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
