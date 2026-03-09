@extends('admin::layouts.app')

@section('title', 'Ordering Settings - General')

@section('body')
<form action="{{ route('admin.settings.general.ordering.update') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PUT')
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
        active="{{ request()->url() }}" />
    <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid gap-4">
            <x-admin::radio.group
                name="ordering_redirect"
                label="{{ __('admin/settings/general.ordering_redirect_label') }}"
                helper="{{ __('admin/settings/general.ordering_redirect_helper') }}"
                required
            >
                <x-admin::radio.option name="ordering_redirect" label="{{ __('admin/settings/general.ordering_redirect_option.complete') }}" value="complete" :checked="Billmora::getGeneral('ordering_redirect') === 'complete'" />
                <x-admin::radio.option name="ordering_redirect" label="{{ __('admin/settings/general.ordering_redirect_option.invoice') }}" value="invoice" :checked="Billmora::getGeneral('ordering_redirect') === 'invoice'" />
                <x-admin::radio.option name="ordering_redirect" label="{{ __('admin/settings/general.ordering_redirect_option.payment') }}" value="payment" :checked="Billmora::getGeneral('ordering_redirect') === 'payment'" />
            </x-admin::radio.group>
            <x-admin::toggle name="ordering_tos" label="{{ __('admin/settings/general.ordering_tos_label') }}" helper="{{ __('admin/settings/general.ordering_tos_helper') }}" :checked="Billmora::getGeneral('ordering_tos')" required />
            <x-admin::toggle name="ordering_notes" label="{{ __('admin/settings/general.ordering_notes_label') }}" helper="{{ __('admin/settings/general.ordering_notes_helper') }}" :checked="Billmora::getGeneral('ordering_notes')" required />
        </div>
        <div class="grid gap-4">
            <x-admin::input 
                name="ordering_number_increment"
                label="{{ __('admin/settings/general.ordering_number_increment_label') }}"
                helper="{{ __('admin/settings/general.ordering_number_increment_helper') }}"
                type="number"
                :value="Billmora::getGeneral('ordering_number_increment')"
                min="1"
                required
            />
            <x-admin::input 
                name="ordering_number_padding"
                label="{{ __('admin/settings/general.ordering_number_padding_label') }}"
                helper="{{ __('admin/settings/general.ordering_number_padding_helper') }}"
                type="number"
                :value="Billmora::getGeneral('ordering_number_padding')"
                min="1"
                required
            />
            <x-admin::input 
                name="ordering_number_format"
                label="{{ __('admin/settings/general.ordering_number_format_label') }}"
                helper="{{ __('admin/settings/general.ordering_number_format_helper') }}"
                type="text"
                :value="Billmora::getGeneral('ordering_number_format')"
                required
            />
        </div>
    </div>
    @can('settings.general.update')
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
