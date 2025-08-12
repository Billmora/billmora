@extends('admin::layouts.app')

@section('body')
    <form action="{{ route('admin.settings.general.term.store') }}" method="POST" class="flex flex-col gap-5">
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
        <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <div class="grid md:grid-cols-2 gap-4">
                <x-admin::toggle name="term_tos" label="{{ __('admin/settings/general.term_tos_label') }}" helper="{{ __('admin/settings/general.term_tos_helper') }}" :checked="Billmora::getGeneral('term_tos')" />
                <x-admin::input type="url" name="term_tos_url" label="{{ __('admin/settings/general.term_tos_url_label') }}" helper="{{ __('admin/settings/general.term_tos_url_helper') }}" value="{{ old('term_tos_url', Billmora::getGeneral('term_tos_url')) }}"/>
            </div>
            <x-admin::editor.text name="term_tos_content" label="{{ __('admin/settings/general.term_tos_content_label') }}" helper="{{ __('admin/settings/general.term_tos_content_helper') }}">{{ old('term_tos_content', Billmora::getGeneral('term_tos_content')) }}</x-admin::editor.text>
        </div>
        <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <div class="grid md:grid-cols-2 gap-4">
                <x-admin::toggle name="term_toc" label="{{ __('admin/settings/general.term_toc_label') }}" helper="{{ __('admin/settings/general.term_toc_helper') }}" :checked="Billmora::getGeneral('term_toc')" />
                <x-admin::input type="url" name="term_toc_url" label="{{ __('admin/settings/general.term_toc_url_label') }}" helper="{{ __('admin/settings/general.term_toc_url_helper') }}" value="{{ old('term_toc_url', Billmora::getGeneral('term_toc_url')) }}"/>
            </div>
            <x-admin::editor.text name="term_toc_content" label="{{ __('admin/settings/general.term_toc_content_label') }}" helper="{{ __('admin/settings/general.term_toc_content_helper') }}">{{ old('term_toc_content', Billmora::getGeneral('term_toc_content')) }}</x-admin::editor.text>
        </div>
        <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <div class="grid md:grid-cols-2 gap-4">
                <x-admin::toggle name="term_privacy" label="{{ __('admin/settings/general.term_privacy_label') }}" helper="{{ __('admin/settings/general.term_privacy_helper') }}" :checked="Billmora::getGeneral('term_privacy')" />
                <x-admin::input type="url" name="term_privacy_url" label="{{ __('admin/settings/general.term_privacy_url_label') }}" helper="{{ __('admin/settings/general.term_privacy_url_helper') }}" value="{{ old('term_privacy_url', Billmora::getGeneral('term_privacy_url')) }}"/>
            </div>
            <x-admin::editor.text name="term_privacy_content" label="{{ __('admin/settings/general.term_privacy_content_label') }}" helper="{{ __('admin/settings/general.term_privacy_content_helper') }}">{{ old('term_privacy_content', Billmora::getGeneral('term_privacy_content')) }}</x-admin::editor.text>
        </div>
        <button type="submit"
            class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.save') }}</button>
    </form>
@endsection
