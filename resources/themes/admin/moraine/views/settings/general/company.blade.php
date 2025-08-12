@extends('admin::layouts.app')

@section('body')
    <form action="{{ route('admin.settings.general.company.store') }}" method="POST" class="flex flex-col gap-5">
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
        <div class="grid md:grid-cols-2 gap-5">
            <div class="flex flex-col gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <x-admin::input type="text" name="company_name"
                    label="{{ __('admin/settings/general.company_name_label') }}"
                    helper="{{ __('admin/settings/general.company_name_helper') }}"
                    value="{{ old('company_name', Billmora::getGeneral('company_name')) }}" required />
                <x-admin::input type="url" name="company_logo"
                    label="{{ __('admin/settings/general.company_logo_label') }}"
                    helper="{{ __('admin/settings/general.company_logo_helper') }}"
                    value="{{ old('company_logo', Billmora::getGeneral('company_logo')) }}" required />
                <x-admin::input type="url" name="company_favicon"
                    label="{{ __('admin/settings/general.company_favicon_label') }}"
                    helper="{{ __('admin/settings/general.company_favicon_helper') }}"
                    value="{{ old('company_favicon', Billmora::getGeneral('company_favicon')) }}" required />
                <x-admin::textarea rows="3" name="company_description"
                    label="{{ __('admin/settings/general.company_description_label') }}"
                    helper="{{ __('admin/settings/general.company_description_helper') }}"
                    required>{{ old('company_description', Billmora::getGeneral('company_description')) }}</x-admin::textarea>
            </div>
            <div class="flex flex-col gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <x-admin::toggle name="company_portal" label="{{ __('admin/settings/general.company_portal_label') }}"
                    helper="{{ __('admin/settings/general.company_portal_helper') }}" :checked="Billmora::getGeneral('company_portal')" />
                <x-admin::select name="company_date_format"
                    label="{{ __('admin/settings/general.company_date_format_label') }}"
                    helper="{{ __('admin/settings/general.company_date_format_helper') }}" required>
                    @foreach (config('utils.date_format') as $value => $label)
                        <option value="{{ $value }}"
                            {{ old('company_date_format', Billmora::getGeneral('company_date_format')) == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </x-admin::select>
                <x-admin::select name="company_language" label="{{ __('admin/settings/general.company_language_label') }}"
                    helper="{{ __('admin/settings/general.company_language_helper') }}" required>
                    @foreach ($langs as $lang)
                        <option value="{{ $lang['lang'] }}"
                            {{ old('company_language', Billmora::getGeneral('company_language')) == $lang['lang'] ? 'selected' : '' }}>
                            {{ $lang['name'] }}</option>
                    @endforeach
                </x-admin::select>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <div class="flex flex-col gap-4">
                <x-admin::toggle name="company_maintenance"
                    label="{{ __('admin/settings/general.company_maintenance_label') }}"
                    helper="{{ __('admin/settings/general.company_maintenance_helper') }}" :checked="Billmora::getGeneral('company_maintenance')" />
                <x-admin::input type="url" name="company_maintenance_url"
                    label="{{ __('admin/settings/general.company_maintenance_url_label') }}"
                    helper="{{ __('admin/settings/general.company_maintenance_url_helper') }}"
                    value="{{ old('company_maintenance_url', Billmora::getGeneral('company_maintenance_url')) }}" />
            </div>
            <x-admin::textarea rows="5" name="company_maintenance_message"
                label="{{ __('admin/settings/general.company_maintenance_message_label') }}"
                helper="{{ __('admin/settings/general.company_maintenance_message_helper') }}">
                {{ old('company_maintenance_message', Billmora::getGeneral('company_maintenance_message')) }}</x-admin::textarea>
        </div>
        <button type="submit"
            class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.save') }}</button>
    </form>
@endsection
