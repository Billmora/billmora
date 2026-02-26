@extends('admin::layouts.app')

@section('title', 'Piping Settings - Ticket')

@section('body')
<form action="{{ route('admin.settings.ticket.piping.update') }}" method="POST" class="flex flex-col gap-5">
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
        <x-admin::toggle 
            name="piping_enabled"
            label="{{ __('admin/settings/ticket.piping_enabled_label') }}"
            helper="{{ __('admin/settings/ticket.piping_enabled_helper') }}"
            checked="{{ old('piping_enabled', Billmora::getTicket('piping_enabled')) }}"
        />
        <div class="grid grid-cols-2 gap-4">
            <x-admin::input 
                name="piping_mail_host"
                label="{{ __('admin/settings/ticket.piping_mail_host_label') }}"
                helper="{{ __('admin/settings/ticket.piping_mail_host_helper') }}"
                value="{{ old('piping_mail_host', config('mail.piping.host')) }}"
            />
            <x-admin::input 
                name="piping_mail_port"
                type="number"
                label="{{ __('admin/settings/ticket.piping_mail_port_label') }}"
                helper="{{ __('admin/settings/ticket.piping_mail_port_helper') }}"
                value="{{ old('piping_mail_port', config('mail.piping.port')) }}"
            />
            <x-admin::input 
                name="piping_mail_address"
                type="email"
                label="{{ __('admin/settings/ticket.piping_mail_address_label') }}"
                helper="{{ __('admin/settings/ticket.piping_mail_address_helper') }}"
                value="{{ old('piping_mail_address', config('mail.piping.address')) }}"
            />
            <x-admin::input 
                name="piping_mail_password"
                type="password"
                label="{{ __('admin/settings/ticket.piping_mail_password_label') }}"
                helper="{{ __('admin/settings/ticket.piping_mail_password_helper') }}"
                value="{{ old('piping_mail_password', config('mail.piping.password')) }}"
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
