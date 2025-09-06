@extends('client::layouts.app')

@section('body')
<div class="grid gap-5">
    @if (session('success'))
        <x-client::alert variant="success">{{ session('success') }}</x-client::alert>
    @endif
    @if (session('warning'))
        <x-client::alert variant="warning">{{ session('warning') }}</x-client::alert>
    @endif
    @if (session('error'))
        <x-client::alert variant="danger">{{ session('error') }}</x-client::alert>
    @endif
    <div class="flex flex-col lg:flex-row gap-5">
        <div class="w-full lg:w-1/4 h-fit flex flex-col gap-6 items-center bg-white p-8 text-center border-2 border-billmora-2 rounded-xl">
            <img src="{{ $user->avatar }}?s=128" alt="user avatar" class="rounded-full w-32 h-auto">
            <div class="flex flex-col">
                <span class="text-xl text-slate-600 font-bold break-all">{{ $user->fullname }}</span>
                <span class="text-md text-slate-500 font-semibold break-all">{{ $user->email }}</span>
            </div>
            <a href="https://gravatar.com/emails" target="_blank" class="w-full flex gap-2 justify-center items-center bg-billmora-primary hover:bg-billmora-primary px-3 py-3 text-white font-semibold rounded-lg transition-colors duration-300">
                <x-lucide-external-link class="w-auto h-5" />
                Change Avatar
            </a>
        </div>
        <form action="{{ route('client.account.settings.update') }}" method="POST" class="w-full lg:w-3/4 bg-white p-8 border-2 border-billmora-2 rounded-xl">
            @csrf
            @method('PUT')
            <div class="grid gap-6">
                <div class="grid gap-4">
                    <h4 class="text-xl font-semibold text-slate-700">{{ __('common.personal_information') }}</h4>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <x-client::input type="text" name="first_name" label="{{ __('common.first_name') }}" value="{{ old('first_name', $user->first_name) }}" required />
                        <x-client::input type="text" name="last_name" label="{{ __('common.last_name') }}" value="{{ old('last_name', $user->last_name) }}" required />
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <x-client::input type="email" name="email" label="{{ __('common.email') }}" value="{{ old('email', $user->email) }}" required disabled />
                        <x-client::input type="tel" name="phone_number" label="{{ __('common.phone_number') }}" value="{{ old('phone_number', $user->billing->phone_number) }}" />
                    </div>
                </div>
                <div class="grid gap-4">
                    <h4 class="text-xl font-semibold text-slate-700">{{ __('common.billing_information') }}</h4>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <x-client::input type="text" name="company_name" label="{{ __('common.company_name') }}" value="{{ old('company_name', $user->billing->company_name) }}" required />
                        <x-client::input type="text" name="street_address_1" label="{{ __('common.street_address_1') }}" value="{{ old('street_address_1', $user->billing->street_address_1) }}" required />
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <x-client::input type="text" name="street_address_2" label="{{ __('common.street_address_2') }}" value="{{ old('street_address_2', $user->billing->street_address_2) }}" />
                        <x-client::input type="text" name="city" label="{{ __('common.city') }}" value="{{ old('city', $user->billing->city) }}" required />
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <x-client::input type="text" name="state" label="{{ __('common.state') }}" value="{{ old('state', $user->billing->state) }}" required />
                        <x-client::input type="number" name="postcode" label="{{ __('common.postcode') }}" value="{{ old('postcode', $user->billing->postcode) }}" required />
                    </div>
                    <x-client::select name="country" label="{{ __('common.country') }}" required>
                        @foreach (config('utils.countries') as $country => $label)
                            <option value="{{ $country }}"
                                {{ old('country', $user->billing->country) == $country ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-client::select>
                </div>
                <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection