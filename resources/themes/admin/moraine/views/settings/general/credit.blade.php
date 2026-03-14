@extends('admin::layouts.app')

@section('title', 'Credit Settings - General')

@section('body')
<form action="{{ route('admin.settings.general.credit.update') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PATCH')
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
                'route' => route('admin.settings.general.service'),
                'icon' => 'lucide-scan-text',
                'label' => __('admin/settings/general.tabs.service'),
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
                'route' => route('admin.settings.general.term'),
                'icon' => 'lucide-badge-check',
                'label' => __('admin/settings/general.tabs.term'),
            ],
            [
                'route' => route('admin.settings.general.social'),
                'icon' => 'lucide-circle-fading-plus',
                'label' => __('admin/settings/general.tabs.social'),
            ],
            [
                'route' => route('admin.settings.general.misc'),
                'icon' => 'lucide-wrench',
                'label' => __('admin/settings/general.tabs.misc'),
            ],
        ]" 
        active="{{ request()->url() }}" />
    <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::toggle name="credit_use" label="{{ __('admin/settings/general.credit_use_label') }}" helper="{{ __('admin/settings/general.credit_use_helper') }}" :checked="Billmora::getGeneral('credit_use')" />
        <x-admin::input 
            type="number" 
            min="1" 
            step="0.01"
            name="credit_min_deposit" 
            label="{{ __('admin/settings/general.credit_min_deposit_label') }}" 
            helper="{{ __('admin/settings/general.credit_min_deposit_helper') }}" 
            value="{{ old('credit_min_deposit', Billmora::getGeneral('credit_min_deposit')) }}" 
            required
        />
        <x-admin::input 
            type="number" 
            min="1" 
            step="0.01"
            name="credit_max_deposit" 
            label="{{ __('admin/settings/general.credit_max_deposit_label') }}" 
            helper="{{ __('admin/settings/general.credit_max_deposit_helper') }}" 
            value="{{ old('credit_max_deposit', Billmora::getGeneral('credit_max_deposit')) }}" 
            required
        />
        <x-admin::input 
            type="number" 
            min="1" 
            step="0.01"
            name="credit_max" 
            label="{{ __('admin/settings/general.credit_max_label') }}" 
            helper="{{ __('admin/settings/general.credit_max_helper') }}" 
            value="{{ old('credit_max', Billmora::getGeneral('credit_max')) }}" 
            required
        />
    </div>
    @can('settings.general.update')
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
