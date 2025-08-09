@extends('admin::layouts.app')

@section('body')
    <form action="{{ route('admin.settings.general.ordering.store') }}" method="POST" class="flex flex-col gap-5">
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
        ]" active="{{ Route::currentRouteName() }}" />
        <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
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
            <div class="grid gap-4">
                <x-admin::input type="number" min="0" name="ordering_grace"
                    label="{{ __('admin/settings/general.ordering_grace_label') }}"
                    helper="{{ __('admin/settings/general.ordering_grace_he lper') }}"
                    value="{{ old('ordering_grace', Billmora::getGeneral('ordering_grace')) }}" required />
                <x-admin::toggle name="ordering_tos" label="{{ __('admin/settings/general.ordering_tos_label') }}" helper="{{ __('admin/settings/general.ordering_tos_helper') }}" :checked="Billmora::getGeneral('ordering_tos')" required />
                <x-admin::toggle name="ordering_notes" label="{{ __('admin/settings/general.ordering_notes_label') }}" helper="{{ __('admin/settings/general.ordering_notes_helper') }}" :checked="Billmora::getGeneral('ordering_notes')" required />
            </div>
        </div>
        <button type="submit"
            class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.save') }}</button>
    </form>
@endsection
