@extends('admin::layouts.app')

@section('body')
    <form action="{{ route('admin.settings.general.social.store') }}" method="POST" class="flex flex-col gap-5">
        @csrf
        @if (session('success'))
            <x-admin::alert variant="success" title="{{ session('success') }}" />
        @endif
        <x-admin::settings.tabs :tabs="[
            [
                'route' => 'admin.settings.general.company',
                'icon' => 'lucide-building',
                'label' => 'admin/settings/general.tabs.company',
            ],
            [
                'route' => 'admin.settings.general.ordering',
                'icon' => 'lucide-truck',
                'label' => 'admin/settings/general.tabs.ordering',
            ],
            [
                'route' => 'admin.settings.general.invoice',
                'icon' => 'lucide-file',
                'label' => 'admin/settings/general.tabs.invoice',
            ],
            [
                'route' => 'admin.settings.general.credit',
                'icon' => 'lucide-badge-cent',
                'label' => 'admin/settings/general.tabs.credit',
            ],
            [
                'route' => 'admin.settings.general.affiliate',
                'icon' => 'lucide-handshake',
                'label' => 'admin/settings/general.tabs.affiliate',
            ],
            [
                'route' => 'admin.settings.general.term',
                'icon' => 'lucide-badge-check',
                'label' => 'admin/settings/general.tabs.term',
            ],
            [
                'route' => 'admin.settings.general.social',
                'icon' => 'lucide-circle-fading-plus',
                'label' => 'admin/settings/general.tabs.social',
            ],
        ]" active="{{ Route::currentRouteName() }}" />
        <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::input type="url" min="1" name="social_discord" label="{{ __('admin/settings/general.social_discord_label') }}" helper="{{ __('admin/settings/general.social_discord_helper') }}" value="{{ old('social_discord', Billmora::getGeneral('social_discord')) }}" required/>
            <x-admin::input type="url" min="1" name="social_youtube" label="{{ __('admin/settings/general.social_youtube_label') }}" helper="{{ __('admin/settings/general.social_youtube_helper') }}" value="{{ old('social_youtube', Billmora::getGeneral('social_youtube')) }}" required/>
            <x-admin::input type="url" min="1" name="social_whatsapp" label="{{ __('admin/settings/general.social_whatsapp_label') }}" helper="{{ __('admin/settings/general.social_whatsapp_helper') }}" value="{{ old('social_whatsapp', Billmora::getGeneral('social_whatsapp')) }}" required/>
            <x-admin::input type="url" min="1" name="social_instagram" label="{{ __('admin/settings/general.social_instagram_label') }}" helper="{{ __('admin/settings/general.social_instagram_helper') }}" value="{{ old('social_instagram', Billmora::getGeneral('social_instagram')) }}" required/>
            <x-admin::input type="url" min="1" name="social_facebook" label="{{ __('admin/settings/general.social_facebook_label') }}" helper="{{ __('admin/settings/general.social_facebook_helper') }}" value="{{ old('social_facebook', Billmora::getGeneral('social_facebook')) }}" required/>
            <x-admin::input type="url" min="1" name="social_linkedin" label="{{ __('admin/settings/general.social_linkedin_label') }}" helper="{{ __('admin/settings/general.social_linkedin_helper') }}" value="{{ old('social_linkedin', Billmora::getGeneral('social_linkedin')) }}" required/>
            <x-admin::input type="url" min="1" name="social_twitter" label="{{ __('admin/settings/general.social_twitter_label') }}" helper="{{ __('admin/settings/general.social_twitter_helper') }}" value="{{ old('social_twitter', Billmora::getGeneral('social_twitter')) }}" required/>
            <x-admin::input type="url" min="1" name="social_github" label="{{ __('admin/settings/general.social_github_label') }}" helper="{{ __('admin/settings/general.social_github_helper') }}" value="{{ old('social_github', Billmora::getGeneral('social_github')) }}" required/>
            <x-admin::input type="url" min="1" name="social_reddit" label="{{ __('admin/settings/general.social_reddit_label') }}" helper="{{ __('admin/settings/general.social_reddit_helper') }}" value="{{ old('social_reddit', Billmora::getGeneral('social_reddit')) }}" required/>
            <x-admin::input type="url" min="1" name="social_skype" label="{{ __('admin/settings/general.social_skype_label') }}" helper="{{ __('admin/settings/general.social_skype_helper') }}" value="{{ old('social_skype', Billmora::getGeneral('social_skype')) }}" required/>
            <x-admin::input type="url" min="1" name="social_telegram" label="{{ __('admin/settings/general.social_telegram_label') }}" helper="{{ __('admin/settings/general.social_telegram_helper') }}" value="{{ old('social_telegram', Billmora::getGeneral('social_telegram')) }}" required/>
        </div>
        <button type="submit"
            class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.save') }}</button>
    </form>
@endsection
