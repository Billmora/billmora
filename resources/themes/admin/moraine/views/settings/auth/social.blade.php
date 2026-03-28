@extends('admin::layouts.app')

@section('title', 'Social Sign In Settings - Auth')

@section('body')
<form action="{{ route('admin.settings.auth.social.update') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PATCH')
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.auth.user'),
                'icon' => 'lucide-user',
                'label' => __('admin/settings/auth.tabs.user'),
            ],
            [
                'route' => route('admin.settings.auth.social'),
                'icon' => 'lucide-globe',
                'label' => __('admin/settings/auth.tabs.social'),
            ],
        ]" 
        active="{{ request()->url() }}" />
    <div class="grid gap-5">
        @foreach (['google', 'discord', 'github'] as $provider)
            <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <span class="text-lg font-semibold text-slate-700">{{ __("admin/settings/auth.social.{$provider}") }}</span>
                <div class="grid gap-4">
                    <x-admin::toggle 
                        name="oauth_{{ $provider }}_enabled"
                        label="{{ __('admin/settings/auth.social.enabled_label') }}"
                        helper="{{ __('admin/settings/auth.social.enabled_helper', ['provider' => $provider]) }}"
                        :checked="Billmora::getAuth('oauth_' . $provider . '_enabled')"
                    />
                    <x-admin::input 
                        type="text" 
                        name="oauth_{{ $provider }}_client_id" 
                        label="{{ __('admin/settings/auth.social.client_id_label') }}" 
                        value="{{ old('oauth_' . $provider . '_client_id', Billmora::getAuth('oauth_' . $provider . '_client_id')) }}" 
                    />
                    <x-admin::input 
                        type="password" 
                        name="oauth_{{ $provider }}_client_secret" 
                        label="{{ __('admin/settings/auth.social.client_secret_label') }}" 
                        value="{{ old('oauth_' . $provider . '_client_secret', Billmora::getAuth('oauth_' . $provider . '_client_secret')) }}" 
                    />
                </div>
            </div>
        @endforeach
    </div>
    @can('settings.auth.update')
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
