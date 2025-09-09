@extends('admin::layouts.app')

@section('title', 'Provider Settings - Captcha')

@section('body')
<form action="{{ route('admin.settings.captcha.provider.store') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.captcha.provider'),
                'icon' => 'lucide-earth-lock',
                'label' => __('admin/settings/captcha.tabs.provider'),
            ],
            [
                'route' => route('admin.settings.captcha.placement'),
                'icon' => 'lucide-waypoints',
                'label' => __('admin/settings/captcha.tabs.placement'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::radio.group
            name="provider_type"
            label="{{ __('admin/settings/captcha.provider_type_label') }}"
            helper="{{ __('admin/settings/captcha.provider_type_helper') }}"
            required
        >
            <x-admin::radio.option name="provider_type" label="Disabled" value="none" :checked="Billmora::getCaptcha('provider_type') === null" />
            <x-admin::radio.option name="provider_type" label="Cloudflare Turnstile" value="turnstile" :checked="Billmora::getCaptcha('provider_type') === 'turnstile'" />
            <x-admin::radio.option name="provider_type" label="reCaptcha v2" value="recaptchav2" :checked="Billmora::getCaptcha('provider_type') === 'recaptchav2'" />
            <x-admin::radio.option name="provider_type" label="hCaptcha" value="hcaptcha" :checked="Billmora::getCaptcha('provider_type') === 'hcaptcha'" />
        </x-admin::radio.group>
    </div>
    <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            type="text"
            name="provider_site_key"
            label="{{ __('admin/settings/captcha.provider_site_key_label') }}"
            helper="{{ __('admin/settings/captcha.provider_site_key_helper') }}"
            value="{{ env('CAPTCHA_SITE_KEY') }}"
            autocomplete="off" />
        <x-admin::input
            type="password"
            name="provider_secret_key"
            label="{{ __('admin/settings/captcha.provider_secret_key_label') }}"
            helper="{{ __('admin/settings/captcha.provider_secret_key_helper') }}"
            value="{{ env('CAPTCHA_SECRET_KEY') }}"
            autocomplete="off" />
    </div>
    <button type="submit"
        class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.save') }}</button>
</form>
@endsection