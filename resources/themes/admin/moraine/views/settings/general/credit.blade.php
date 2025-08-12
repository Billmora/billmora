@extends('admin::layouts.app')

@section('body')
    <form action="{{ route('admin.settings.general.credit.store') }}" method="POST" class="flex flex-col gap-5">
        @csrf
        @if (session('success'))
            <x-admin::alert variant="success" title="{{ session('success') }}" />
        @endif
        <x-admin::tabs 
            :tabs="[
                [
                    'route' => route('admin.settings.general.company'),
                    'icon' => 'lucide-building',
                    'label' => __('admin/settings/general.tabs.company'),
                ],
                [
                    'route' => route('admin.settings.general.ordering'),
                    'icon' => 'lucide-truck',
                    'label' => __('admin/settings/general.tabs.ordering'),
                ],
                [
                    'route' => route('admin.settings.general.invoice'),
                    'icon' => 'lucide-file',
                    'label' => __('admin/settings/general.tabs.invoice'),
                ],
                [
                    'route' => route('admin.settings.general.credit'),
                    'icon' => 'lucide-badge-cent',
                    'label' => __('admin/settings/general.tabs.credit'),
                ],
                [
                    'route' => route('admin.settings.general.affiliate'),
                    'icon' => 'lucide-handshake',
                    'label' => __('admin/settings/general.tabs.affiliate'),
                ],
                [
                    'route' => route('admin.settings.general.term'),
                    'icon' => 'lucide-badge-check',
                    'label' => __('admin/settings/general.tabs.term'),
                ],
                [
                    'route' => route('admin.settings.general.social'),
                    'icon' => 'lucide-circle-fading-plus',
                    'label' => __('admin/settings/general.tabs.social'),
                ],
            ]" 
            active="{{ request()->fullUrl() }}" />
        <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::toggle name="credit_use" label="{{ __('admin/settings/general.credit_use_label') }}" helper="{{ __('admin/settings/general.credit_use_helper') }}" :checked="Billmora::getGeneral('credit_use')" />
            <x-admin::input type="number" min="1" name="credit_min_deposit" label="{{ __('admin/settings/general.credit_min_deposit_label') }}" helper="{{ __('admin/settings/general.credit_min_deposit_helper') }}" value="{{ old('credit_min_deposit', Billmora::getGeneral('credit_min_deposit')) }}" required/>
            <x-admin::input type="number" min="1" max="1000000" name="credit_max_deposit" label="{{ __('admin/settings/general.credit_max_deposit_label') }}" helper="{{ __('admin/settings/general.credit_max_deposit_helper') }}" value="{{ old('credit_max_deposit', Billmora::getGeneral('credit_max_deposit')) }}" required/>
            <x-admin::input type="number" min="1" max="10000000" name="credit_max" label="{{ __('admin/settings/general.credit_max_label') }}" helper="{{ __('admin/settings/general.credit_max_helper') }}" value="{{ old('credit_max', Billmora::getGeneral('credit_max')) }}" required/>
        </div>
        <button type="submit"
            class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.save') }}</button>
    </form>
@endsection
