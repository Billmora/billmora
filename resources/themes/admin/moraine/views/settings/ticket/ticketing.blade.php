@extends('admin::layouts.app')

@section('title', 'Ticketing Settings - Ticket')

@section('body')
<form action="{{ route('admin.settings.ticket.ticketing.update') }}" method="POST" class="flex flex-col gap-5">
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
        ]" 
        active="{{ request()->url() }}" />
    <div class="grid grid-cols-1 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::tags 
            name="ticketing_departements"
            label="{{ __('admin/settings/ticket.ticketing_departements_label') }}"
            helper="{{ __('admin/settings/ticket.ticketing_departements_helper') }}"
            :value="old('ticketing_departements', Billmora::getTicket('ticketing_departements'))"
            required
        />
        <div class="grid grid-cols-2 gap-4">
            <x-admin::input 
                name="ticketing_number_increment"
                type="number"
                min="1"
                label="{{ __('admin/settings/ticket.ticketing_number_increment_label') }}"
                helper="{{ __('admin/settings/ticket.ticketing_number_increment_helper') }}"
                value="{{ old('ticketing_number_increment', Billmora::getTicket('ticketing_number_increment')) }}"
                required
            />
            <x-admin::input 
                name="ticketing_number_padding"
                type="number"
                min="1"
                label="{{ __('admin/settings/ticket.ticketing_number_padding_label') }}"
                helper="{{ __('admin/settings/ticket.ticketing_number_padding_helper') }}"
                value="{{ old('ticketing_number_padding', Billmora::getTicket('ticketing_number_padding')) }}"
                required
            />
            <x-admin::input 
                name="ticketing_number_format"
                label="{{ __('admin/settings/ticket.ticketing_number_format_label') }}"
                helper="{{ __('admin/settings/ticket.ticketing_number_format_helper') }}"
                value="{{ old('ticketing_number_format', Billmora::getTicket('ticketing_number_format')) }}"
                required
            />
            <x-admin::toggle 
                name="ticketing_allow_client_close"
                label="{{ __('admin/settings/ticket.ticketing_allow_client_close_label') }}"
                helper="{{ __('admin/settings/ticket.ticketing_allow_client_close_helper') }}"
                checked="{{ old('ticketing_allow_client_close', Billmora::getTicket('ticketing_allow_client_close')) }}"
            />
        </div>
    </div>
    @can('settings.ticket.update')
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
