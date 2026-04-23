@extends('admin::layouts.app')

@section('title', 'Domain Settings - General')

@section('body')
<form action="{{ route('admin.settings.general.domain.update') }}" method="POST" class="flex flex-col gap-5">
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
                'route' => route('admin.settings.general.domain'),
                'icon' => 'lucide-globe',
                'label' => __('admin/settings/general.tabs.domain'),
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
    <div class="grid md:grid-cols-2 gap-8 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="flex flex-col gap-4">
            <x-admin::toggle 
                name="domain_registration_enabled"
                label="{{ __('admin/settings/general.domain_registration_enabled_label') }}"
                helper="{{ __('admin/settings/general.domain_registration_enabled_helper') }}"
                :checked="Billmora::getGeneral('domain_registration_enabled')"
            />
            <x-admin::toggle 
                name="domain_transfer_enabled"
                label="{{ __('admin/settings/general.domain_transfer_enabled_label') }}"
                helper="{{ __('admin/settings/general.domain_transfer_enabled_helper') }}"
                :checked="Billmora::getGeneral('domain_transfer_enabled')"
            />
            <x-admin::input 
                name="domain_number_increment"
                label="{{ __('admin/settings/general.domain_number_increment_label') }}"
                helper="{{ __('admin/settings/general.domain_number_increment_helper') }}"
                type="number"
                :value="Billmora::getGeneral('domain_number_increment')"
                min="1"
                required
            />
            <x-admin::input 
                name="domain_number_padding"
                label="{{ __('admin/settings/general.domain_number_padding_label') }}"
                helper="{{ __('admin/settings/general.domain_number_padding_helper') }}"
                type="number"
                :value="Billmora::getGeneral('domain_number_padding')"
                min="1"
                required
            />
            <x-admin::input 
                name="domain_number_format"
                label="{{ __('admin/settings/general.domain_number_format_label') }}"
                helper="{{ __('admin/settings/general.domain_number_format_helper') }}"
                type="text"
                :value="Billmora::getGeneral('domain_number_format')"
                required
            />
        </div>

        <div class="flex flex-col gap-4">
            @for ($i = 1; $i <= 5; $i++)
                <x-admin::input 
                    name="domain_nameserver_{{ $i }}"
                    label="{{ __('admin/settings/general.domain_nameserver_label', ['number' => $i]) }}"
                    helper="{{ __('admin/settings/general.domain_nameserver_helper', ['number' => $i]) }}"
                    type="text"
                    :value="Billmora::getGeneral('domain_nameserver_' . $i)"
                    placeholder="ns{{ $i }}.example.com"
                />
            @endfor
        </div>
    </div>
    @can('settings.general.update')
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
