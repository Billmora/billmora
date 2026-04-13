@extends('admin::layouts.app')

@section('title', 'Invoice Settings - General')

@section('body')
<form action="{{ route('admin.settings.general.invoice.update') }}" method="POST" class="flex flex-col gap-5">
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
            ],            [
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
    <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid gap-4 h-fit">
            <x-admin::toggle name="invoice_pdf" label="{{ __('admin/settings/general.invoice_pdf_label') }}" helper="{{ __('admin/settings/general.invoice_pdf_helper') }}" :checked="Billmora::getGeneral('invoice_pdf')" />
            <x-admin::radio.group name="invoice_pdf_size" label="{{ __('admin/settings/general.invoice_pdf_size_label') }}" helper="{{ __('admin/settings/general.invoice_pdf_size_helper') }}" required>
                <x-admin::radio.option name="invoice_pdf_size" label="A4" value="A4" :checked="Billmora::getGeneral('invoice_pdf_size') === 'A4'" />
                <x-admin::radio.option name="invoice_pdf_size" label="Letter" value="letter" :checked="Billmora::getGeneral('invoice_pdf_size') === 'letter'" />
            </x-admin::radio.group>
        </div>
        <div class="grid gap-4">
            <x-admin::input 
                name="invoice_number_increment"
                label="{{ __('admin/settings/general.invoice_number_increment_label') }}"
                helper="{{ __('admin/settings/general.invoice_number_increment_helper') }}"
                type="number"
                :value="Billmora::getGeneral('invoice_number_increment')"
                min="1"
                required
            />
            <x-admin::input 
                name="invoice_number_padding"
                label="{{ __('admin/settings/general.invoice_number_padding_label') }}"
                helper="{{ __('admin/settings/general.invoice_number_padding_helper') }}"
                type="number"
                :value="Billmora::getGeneral('invoice_number_padding')"
                min="1"
                required
            />
            <x-admin::input 
                name="invoice_number_format"
                label="{{ __('admin/settings/general.invoice_number_format_label') }}"
                helper="{{ __('admin/settings/general.invoice_number_format_helper') }}"
                type="text"
                :value="Billmora::getGeneral('invoice_number_format')"
                required
            />
        </div>
    </div>
    @can('settings.general.update')
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
