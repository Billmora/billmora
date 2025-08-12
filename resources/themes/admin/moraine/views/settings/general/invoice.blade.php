@extends('admin::layouts.app')

@section('body')
    <form action="{{ route('admin.settings.general.invoice.store') }}" method="POST" class="flex flex-col gap-5">
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
        ]" active="{{ Route::currentRouteName() }}" />
        <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::toggle name="invoice_pdf" label="{{ __('admin/settings/general.invoice_pdf_label') }}" helper="{{ __('admin/settings/general.invoice_pdf_helper') }}" :checked="Billmora::getGeneral('invoice_pdf')" />
            <x-admin::radio.group name="invoice_pdf_size" label="{{ __('admin/settings/general.invoice_pdf_size_label') }}" helper="{{ __('admin/settings/general.invoice_pdf_size_helper') }}" required>
                <x-admin::radio.option name="invoice_pdf_size" label="A4" value="A4" :checked="Billmora::getGeneral('invoice_pdf_size') === 'A4'" />
                <x-admin::radio.option name="invoice_pdf_size" label="Letter" value="letter" :checked="Billmora::getGeneral('invoice_pdf_size') === 'letter'" />
            </x-admin::radio.group>
            <x-admin::input name="invoice_pdf_font" label="{{ __('admin/settings/general.invoice_pdf_font_label') }}" helper="{{ __('admin/settings/general.invoice_pdf_font_helper') }}" value="{{ old('invoice_pdf_font', Billmora::getGeneral('invoice_pdf_font')) }}" required />
            <x-admin::toggle name="invoice_mass_payment" label="{{ __('admin/settings/general.invoice_mass_payment_label') }}" helper="{{ __('admin/settings/general.invoice_mass_payment_helper') }}" :checked="Billmora::getGeneral('invoice_mass_payment')" />
            <x-admin::toggle name="invoice_choose_payment" label="{{ __('admin/settings/general.invoice_choose_payment_label') }}" helper="{{ __('admin/settings/general.invoice_choose_payment_helper') }}" :checked="Billmora::getGeneral('invoice_choose_payment')" />
            <x-admin::toggle name="invoice_cancelation_handling" label="{{ __('admin/settings/general.invoice_cancelation_handling_label') }}" helper="{{ __('admin/settings/general.invoice_cancelation_handling_helper') }}" :checked="Billmora::getGeneral('invoice_cancelation_handling')" />
        </div>
        <button type="submit"
            class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.save') }}</button>
    </form>
@endsection
