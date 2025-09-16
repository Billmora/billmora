@extends('admin::layouts.app')

@section('title', 'User Settings - Auth')

@section('body')
<form action="{{ route('admin.settings.auth.user.store') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.auth.user'),
                'icon' => 'lucide-user',
                'label' => __('admin/settings/auth.tabs.user'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid gap-4">
            <x-admin::toggle name="user_registration" label="{{ __('admin/settings/auth.user_registration_label') }}" helper="{{ __('admin/settings/auth.user_registration_helper') }}" :checked="Billmora::getAuth('user_registration')" />
            <x-admin::toggle name="user_require_verified" label="{{ __('admin/settings/auth.user_require_verified_label') }}" helper="{{ __('admin/settings/auth.user_require_verified_helper') }}" :checked="Billmora::getAuth('user_require_verified')" />
            <x-admin::toggle name="user_require_two_factor" label="{{ __('admin/settings/auth.user_require_two_factor_label') }}" helper="{{ __('admin/settings/auth.user_require_two_factor_helper') }}" :checked="Billmora::getAuth('user_require_two_factor')" />
        </div>
        <div class="grid gap-4">
            <x-admin::multiselect
                    name="user_registration_disabled_inputs"
                    :options="[
                        ['value' => 'phone_number', 'title' => 'Phone Number'],
                        ['value' => 'company_name', 'title' => 'Company Name'],
                        ['value' => 'street_address_1', 'title' => 'Street Address 1'],
                        ['value' => 'street_address_2', 'title' => 'Street Address 2'],
                        ['value' => 'city', 'title' => 'City'],
                        ['value' => 'state', 'title' => 'State'],
                        ['value' => 'postcode', 'title' => 'Postcode'],
                        ['value' => 'country', 'title' => 'Country'],
                    ]"
                    :selected="old('user_registration_disabled_inputs', Billmora::getAuth('user_registration_disabled_inputs'))"
                    label="{{ __('admin/settings/auth.user_registration_disabled_inputs_label') }}"
                    helper="{{ __('admin/settings/auth.user_registration_disabled_inputs_helper') }}"
                />
            <x-admin::multiselect
                    name="user_billing_required_inputs"
                    :options="[
                        ['value' => 'phone_number', 'title' => 'Phone Number'],
                        ['value' => 'company_name', 'title' => 'Company Name'],
                        ['value' => 'street_address_1', 'title' => 'Street Address 1'],
                        ['value' => 'street_address_2', 'title' => 'Street Address 2'],
                        ['value' => 'city', 'title' => 'City'],
                        ['value' => 'state', 'title' => 'State'],
                        ['value' => 'postcode', 'title' => 'Postcode'],
                        ['value' => 'country', 'title' => 'Country'],
                    ]"
                    :selected="old('user_billing_required_inputs', Billmora::getAuth('user_billing_required_inputs'))"
                    label="{{ __('admin/settings/auth.user_billing_required_inputs_label') }}"
                    helper="{{ __('admin/settings/auth.user_billing_required_inputs_helper') }}"
                />
        </div>
    </div>
    <button type="submit"
        class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.save') }}</button>
</form>
@endsection