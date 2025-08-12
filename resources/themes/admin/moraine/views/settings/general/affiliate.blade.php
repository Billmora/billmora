@extends('admin::layouts.app')

@section('body')
    <form action="{{ route('admin.settings.general.affiliate.store') }}" method="POST" class="flex flex-col gap-5">
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
            <x-admin::toggle name="affiliate_use" label="{{ __('admin/settings/general.affiliate_use_label') }}" helper="{{ __('admin/settings/general.affiliate_use_helper') }}" :checked="Billmora::getGeneral('affiliate_use')" />
            <x-admin::input type="number" min="1" name="affiliate_min_payment" label="{{ __('admin/settings/general.affiliate_min_payment_label') }}" helper="{{ __('admin/settings/general.affiliate_min_payment_helper') }}" value="{{ old('affiliate_min_payment', Billmora::getGeneral('affiliate_min_payment')) }}" required/>
            <x-admin::input type="number" min="1" max="100" name="affiliate_reward" label="{{ __('admin/settings/general.affiliate_reward_label') }}" helper="{{ __('admin/settings/general.affiliate_reward_helper') }}" value="{{ old('affiliate_reward', Billmora::getGeneral('affiliate_reward')) }}" required/>
            <x-admin::input type="number" min="1" max="100" name="affiliate_discount" label="{{ __('admin/settings/general.affiliate_discount_label') }}" helper="{{ __('admin/settings/general.affiliate_discount_helper') }}" value="{{ old('affiliate_discount', Billmora::getGeneral('affiliate_discount')) }}" required/>
        </div>
        <button type="submit"
            class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.save') }}</button>
    </form>
@endsection
