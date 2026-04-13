@extends('admin::layouts.app')

@section('title', 'Mailer Settings - Mail')

@section('body')
<form 
    action="{{ route('admin.settings.mail.mailer.update') }}" 
    method="POST" 
    class="flex flex-col gap-5"
    x-data="{ 
        selectedDriver: '{{ old('mailer_driver', config('mail.default')) }}' 
    }"
    x-init="$watch('selectedDriver')"
>
    @csrf
    @method('PUT')
    @if (session('failed'))
        <x-admin::alert variant="danger" title="{{ __('common.send_failed', ['attribute' =>  __('admin/settings/mail.mailer_test_label')]) }}">
            {{ session('failed') }}
        </x-admin::alert>
    @endif
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.mail.mailer'),
                'icon' => 'lucide-send',
                'label' => __('admin/settings/mail.tabs.mailer'),
            ],
            [
                'route' => route('admin.settings.mail.notification'),
                'icon' => 'lucide-mailbox',
                'label' => __('admin/settings/mail.tabs.notification'),
            ],
        ]" 
        active="{{ request()->url() }}" 
    />
    <x-admin::alert variant="primary" title="{{ __('admin/settings/mail.mailer_alert_label') }}">
        {{ __('admin/settings/mail.mailer_alert_helper') }}
    </x-admin::alert>
    <div class="grid lg:grid-cols-3 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div x-on:change="selectedDriver = $event.target.value">
            <x-admin::radio.group
                name="mailer_driver"
                label="{{ __('admin/settings/mail.mailer_driver_label') }}"
                helper="{{ __('admin/settings/mail.mailer_driver_helper') }}"
                required
            >
                <x-admin::radio.option 
                    name="mailer_driver" 
                    label="SMTP" 
                    value="smtp" 
                    :checked="old('mailer_driver', config('mail.default')) === 'smtp'" 
                />
                <x-admin::radio.option 
                    name="mailer_driver" 
                    label="Mailgun" 
                    value="mailgun" 
                    :checked="old('mailer_driver', config('mail.default')) === 'mailgun'" 
                />
            </x-admin::radio.group>
        </div>
        <x-admin::input 
            type="email"
            name="mailer_from_address"
            label="{{ __('admin/settings/mail.mailer_from_address_label') }}"
            helper="{{ __('admin/settings/mail.mailer_from_address_helper') }}"
            value="{{ old('mailer_from_address', config('mail.from.address')) }}"
            required
        />
        <x-admin::input 
            type="text"
            name="mailer_from_name"
            label="{{ __('admin/settings/mail.mailer_from_name_label') }}"
            helper="{{ __('admin/settings/mail.mailer_from_name_helper') }}"
            value="{{ old('mailer_from_name', config('mail.from.name')) }}"
            required
        />
    </div>
    <div class="grid md:grid-cols-1 gap-5">
        @can('settings.mail.update')
            <x-admin::modal.trigger type="button" modal="testConnectionModal" class="flex items-center gap-2 bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-cable class="w-auto h-5" />
                {{ __('admin/settings/mail.mailer_test_label') }}
            </x-admin::modal.trigger>
        @endcan
        <!-- SMTP Configuration -->
        <div 
            class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl"
            x-show="selectedDriver === 'smtp'"
        >
            <div class="grid md:grid-cols-3 gap-4">
                <x-admin::input 
                    type="text"
                    name="mailer_smtp_host"
                    label="{{ __('admin/settings/mail.mailer_smtp_host_label') }}"
                    helper="{{ __('admin/settings/mail.mailer_smtp_host_helper') }}" 
                    value="{{ old('mailer_smtp_host', config('mail.mailers.smtp.host')) }}"
                    required
                />
                <x-admin::input 
                    type="number" 
                    name="mailer_smtp_port" 
                    label="{{ __('admin/settings/mail.mailer_smtp_port_label') }}" 
                    helper="{{ __('admin/settings/mail.mailer_smtp_port_helper') }}" 
                    value="{{ old('mailer_smtp_port', config('mail.mailers.smtp.port')) }}" 
                    required
                />
                <x-admin::select 
                    name="mailer_smtp_encryption" 
                    label="{{ __('admin/settings/mail.mailer_smtp_encryption_label') }}" 
                    helper="{{ __('admin/settings/mail.mailer_smtp_encryption_helper') }}"
                    required
                >
                    <option value="tls" {{ old('mailer_smtp_encryption', config('mail.mailers.smtp.encryption')) == 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ old('mailer_smtp_encryption', config('mail.mailers.smtp.encryption')) == 'ssl' ? 'selected' : '' }}>SSL</option>
                </x-admin::select>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <x-admin::input 
                    type="text" 
                    name="mailer_smtp_username"
                    label="{{ __('admin/settings/mail.mailer_smtp_username_label') }}" 
                    helper="{{ __('admin/settings/mail.mailer_smtp_username_helper') }}"
                    value="{{ old('mailer_smtp_username', config('mail.mailers.smtp.username')) }}"
                    required
                />
                <x-admin::input 
                    type="password" 
                    name="mailer_smtp_password" 
                    label="{{ __('admin/settings/mail.mailer_smtp_password_label') }}" 
                    helper="{{ __('admin/settings/mail.mailer_smtp_password_helper') }}" 
                    value="{{ old('mailer_smtp_password', config('mail.mailers.smtp.password')) }}" 
                    required
                />
            </div>
        </div>
        <!-- Mailgun Configuration -->
        <div 
            class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl"
            x-show="selectedDriver === 'mailgun'"
        >
            <x-admin::input 
                type="text" 
                name="mailer_mailgun_domain" 
                label="{{ __('admin/settings/mail.mailer_mailgun_domain_label') }}" 
                helper="{{ __('admin/settings/mail.mailer_mailgun_domain_helper') }}" 
                value="{{ old('mailer_mailgun_domain', config('mail.mailers.mailgun.domain')) }}"
                required
            />
            <x-admin::input 
                type="password" 
                name="mailer_mailgun_secret" 
                label="{{ __('admin/settings/mail.mailer_mailgun_secret_label') }}" 
                helper="{{ __('admin/settings/mail.mailer_mailgun_secret_helper') }}" 
                value="{{ old('mailer_mailgun_secret',config('mail.mailers.mailgun.secret')) }}" 
                required
            />
            <x-admin::input 
                type="text" 
                name="mailer_mailgun_endpoint" 
                label="{{ __('admin/settings/mail.mailer_mailgun_endpoint_label') }}" 
                helper="{{ __('admin/settings/mail.mailer_mailgun_endpoint_helper') }}" 
                value="{{ old('mailer_mailgun_endpoint', config('mail.mailers.mailgun.endpoint')) }}"
                required
            />
        </div>
    </div>
    <div class="flex gap-2 ml-auto">
        @can('settings.mail.update')
            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.save') }}
            </button>
        @endcan
    </div>
</form>
@can('settings.mail.update')
    <x-admin::modal.content
        modal="testConnectionModal"
        variant="danger"
        size="xl"
        position="centered"
        title="{{ __('common.confirm_modal_title')}}"
        description="{{ __('common.confirm_modal_description', ['item' => __('admin/settings/mail.mailer_test_label')]) }}"
    >
        <form action="{{ route('admin.settings.mail.mailer.test') }}" method="POST">
            @csrf
            <div class="flex justify-end gap-2 mt-4">
                <x-admin::modal.trigger 
                    type="button" 
                    variant="close" 
                    class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                >
                    {{ __('common.cancel') }}
                </x-admin::modal.trigger>
                <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.submit') }}
                </button>
            </div>
        </form>
    </x-admin::modal.content>
@endcan
@endsection
