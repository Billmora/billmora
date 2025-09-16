@extends('admin::layouts.app')

@section('title', "User Profile - $user->email")

@section('body')
<div class="flex flex-col gap-5">
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    @if (session('error'))
        <x-admin::alert variant="danger" title="{{ session('error') }}" />
    @endif
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.users.summary', ['id' => $user->id]),
                'icon' => 'lucide-contact',
                'label' => __('admin/users/edit.tabs.summary'),
            ],
            [
                'route' => route('admin.users.profile', ['id' => $user->id]),
                'icon' => 'lucide-user-pen',
                'label' => __('admin/users/edit.tabs.profile'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    @if (!$user->isEmailVerified())
        <x-admin::alert variant="warning" title="{{ __('admin/users/edit.email_verification_alert_label') }}">
            {{ __('admin/users/edit.email_verification_alert_helper') }}
            <form action="" method="POST" class="ml-auto">
                @csrf
                <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('admin/users/edit.marked_as_verified') }}</button>
            </form>
        </x-admin::alert>
    @endif
    <form action="{{ route('admin.users.profile.update', ['id' => $user->id]) }}" method="POST" class="flex flex-col gap-5">
        @csrf
        @method('PUT')
        <div class="flex flex-col lg:flex-row gap-5">
            <div class="w-full lg:w-2/3 h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <div class="grid grid-cols-none md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-4">
                        <x-client::input type="text" name="first_name" label="{{ __('common.first_name') }}" value="{{ old('first_name', $user->first_name) }}" required />
                        <x-client::input type="text" name="last_name" label="{{ __('common.last_name') }}" value="{{ old('last_name', $user->last_name) }}" required />
                        <x-client::input type="email" name="email" label="{{ __('common.email') }}" value="{{ old('email', $user->email) }}" required />
                        <x-client::input type="password" name="password" label="{{ __('common.password') }}" />
                    </div>
                    <div class="flex flex-col gap-4">
                        <x-client::input type="tel" name="phone_number" label="{{ __('common.phone_number') }}" value="{{ old('phone_number', $user->billing->phone_number) }}" />
                        <x-client::input type="text" name="company_name" label="{{ __('common.company_name') }}" value="{{ old('company_name', $user->billing->company_name) }}" required />
                        <x-client::input type="text" name="street_address_1" label="{{ __('common.street_address_1') }}" value="{{ old('street_address_1', $user->billing->street_address_1) }}" required />
                        <x-client::input type="text" name="street_address_2" label="{{ __('common.street_address_2') }}" value="{{ old('street_address_2', $user->billing->street_address_2) }}" />
                        <x-client::input type="text" name="city" label="{{ __('common.city') }}" value="{{ old('city', $user->billing->city) }}" required />
                        <x-client::input type="text" name="state" label="{{ __('common.state') }}" value="{{ old('state', $user->billing->state) }}" required />
                        <x-client::input type="number" name="postcode" label="{{ __('common.postcode') }}" value="{{ old('postcode', $user->billing->postcode) }}" required />
                        <x-client::select name="country" label="{{ __('common.country') }}" required>
                            @foreach (config('utils.countries') as $country => $label)
                                <option value="{{ $country }}"
                                    {{ old('country', $user->billing->country) == $country ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </x-client::select>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-1/3 h-fit flex flex-col gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                {{-- TODO: Add old value from user status --}}
                <x-client::select name="status" label="{{ __('common.status') }}" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                    <option value="closed">Closed</option>
                </x-client::select>
                {{-- TODO: List currency from data and add old value --}}
                <x-client::select name="currency" label="{{ __('common.currency') }}" required>
                    <option value="IDR">IDR</option>
                </x-client::select>
                {{-- TODO: Add old value default fron user language --}}
                <x-client::select name="language" label="{{ __('common.language') }}" required>
                    @foreach ($langs as $lang)
                        <option value="{{ $lang['lang'] }}"
                            {{ old('language') }}>
                            {{ $lang['name'] }}
                        </option>
                    @endforeach
                </x-client::select>
            </div>
        </div>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.save') }}</button>
    </form>
</div>
@endsection