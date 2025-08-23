@extends('admin::layouts.app')

@section('body')
    <form action="{{ route('admin.settings.mail.mailer.store') }}" method="POST" class="flex flex-col gap-5">
        @csrf
        @if (session('success'))
            <x-admin::alert variant="success" title="{{ session('success') }}" />
        @endif
        <x-admin::tabs 
            :tabs="[
                [
                    'route' => route('admin.settings.mail.mailer'),
                    'icon' => 'lucide-send',
                    'label' => __('admin/settings/mail.tabs.mailer'),
                ],
                [
                    'route' => route('admin.settings.mail.template'),
                    'icon' => 'lucide-mailbox',
                    'label' => __('admin/settings/mail.tabs.template'),
                ],
            ]" 
            active="{{ request()->fullUrl() }}" />
            <x-admin::alert variant="primary" title="{{ __('admin/settings/mail.mailer_alert_label') }}">{{ __('admin/settings/mail.mailer_alert_helper') }}</x-admin::alert>
            <div class="grid lg:grid-cols-3 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <x-admin::radio.group
                    name="mailer_driver"
                    label="{{ __('admin/settings/mail.mailer_driver_label') }}"
                    helper="{{ __('admin/settings/mail.mailer_driver_helper') }}"
                    required
                >
                    <x-admin::radio.option name="mailer_driver" label="SMTP" value="smtp" :checked="env('MAIL_MAILER') === 'smtp'" />
                    <x-admin::radio.option name="mailer_driver" label="Mailgun" value="mailgun" :checked="env('MAIL_MAILER') === 'mailgun'" />
                </x-admin::radio.group>
                <x-admin::input type="email" name="mailer_from_address" label="{{ __('admin/settings/mail.mailer_from_address_label') }}" helper="{{ __('admin/settings/mail.mailer_from_address_helper') }}" value="{{ old('mailer_from_address', env('MAIL_FROM_ADDRESS')) }}" required />
                <x-admin::input type="text" name="mailer_from_name" label="{{ __('admin/settings/mail.mailer_from_name_label') }}" helper="{{ __('admin/settings/mail.mailer_from_name_helper') }}" value="{{ old('mailer_from_name', env('MAIL_FROM_NAME')) }}" required />
            </div>
            <div class="grid md:grid-cols-2 gap-5">
                <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    <div class="grid md:grid-cols-2 gap-4">
                        <x-admin::input type="text" name="mailer_smtp_host" label="{{ __('admin/settings/mail.mailer_smtp_host_label') }}" helper="{{ __('admin/settings/mail.mailer_smtp_host_helper') }}" value="{{ old('mailer_smtp_host', env('MAIL_HOST')) }}" />
                        <x-admin::input type="number" name="mailer_smtp_port" label="{{ __('admin/settings/mail.mailer_smtp_port_label') }}" helper="{{ __('admin/settings/mail.mailer_smtp_port_helper') }}" value="{{ old('mailer_smtp_port', env('MAIL_PORT')) }}" />
                    </div>
                    <x-admin::select name="mailer_smtp_encryption" label="{{ __('admin/settings/mail.mailer_smtp_encryption_label') }}" helper="{{ __('admin/settings/mail.mailer_smtp_encryption_helper') }}">
                        <option value="tls" {{ old('mailer_smtp_encryption', env('MAIL_ENCRYPTION')) == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('mailer_smtp_encryption', env('MAIL_ENCRYPTION')) == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="" {{ old('mailer_smtp_encryption', env('MAIL_ENCRYPTION')) == '' ? 'selected' : '' }}>{{ __('admin/common.none') }}</option>
                    </x-admin::select>
                    <x-admin::input type="text" name="mailer_smtp_username" label="{{ __('admin/settings/mail.mailer_smtp_username_label') }}" helper="{{ __('admin/settings/mail.mailer_smtp_username_helper') }}" value="{{ old('mailer_smtp_username', env('MAIL_USERNAME')) }}" />
                    <x-admin::input type="password" name="mailer_smtp_password" label="{{ __('admin/settings/mail.mailer_smtp_password_label') }}" helper="{{ __('admin/settings/mail.mailer_smtp_password_helper') }}" value="{{ old('mailer_smtp_password', env('MAIL_PASSWORD')) }}" />
                </div>
                <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    <x-admin::input type="text" name="mailer_mailgun_domain" label="{{ __('admin/settings/mail.mailer_mailgun_domain_label') }}" helper="{{ __('admin/settings/mail.mailer_mailgun_domain_helper') }}" value="{{ old('mailer_mailgun_domain', env('MAILGUN_DOMAIN')) }}" />
                    <x-admin::input type="password" name="mailer_mailgun_secret" label="{{ __('admin/settings/mail.mailer_mailgun_secret_label') }}" helper="{{ __('admin/settings/mail.mailer_mailgun_secret_helper') }}" value="{{ old('mailer_mailgun_secret', env('MAILGUN_SECRET')) }}" />
                    <x-admin::input type="text" name="mailer_mailgun_endpoint" label="{{ __('admin/settings/mail.mailer_mailgun_endpoint_label') }}" helper="{{ __('admin/settings/mail.mailer_mailgun_endpoint_helper') }}" value="{{ old('mailer_mailgun_endpoint', env('MAILGUN_ENDPOINT')) }}" />
                </div>
            </div>
        <button type="submit"
            class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.save') }}</button>
    </form>
@endsection
