<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('client::layouts.meta')
</head>
<body class="bg-white">
    <div class="flex flex-col lg:flex-row w-full min-h-dvh lg:items-start p-8 lg:p-0">
        <form action="{{ route('client.register.store') }}" method="POST" class="w-full lg:w-1/2 h-auto lg:p-8">
            @csrf
            <div class="max-w-180 h-full flex flex-col justify-between mx-auto">
                <a href="#" class="flex gap-2 items-center mb-10 lg:mb-0 text-slate-500 font-semibold">
                    <x-lucide-chevron-left class="w-auto h-5" />
                    <span>{{ __('common.back_to', ['page' => __('common.page.portal')]) }}</span>
                </a>
                <div class="grid gap-6 my-10 lg:my-20">
                    @if (session('success'))
                        <x-client::alert variant="success">{{ session('success') }}</x-client::alert>
                    @endif
                    @if (session('error'))
                        <x-client::alert variant="danger">{{ session('error') }}</x-client::alert>
                    @endif
                    <h3 class="mb-2 font-semibold text-2xl text-slate-700">{{ __('auth.page.register') }}</h3>
                    <div class="grid gap-4">
                        <h4 class="text-xl font-semibold text-slate-700">{{ __('common.personal_information') }}</h4>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <x-client::input type="text" name="first_name" label="{{ __('common.first_name') }}" value="{{ old('first_name') }}" required />
                            <x-client::input type="text" name="last_name" label="{{ __('common.last_name') }}" value="{{ old('last_name') }}" required />
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <x-client::input type="email" name="email" label="{{ __('common.email') }}" value="{{ old('email') }}" required />
                            <x-client::input type="tel" name="phone_number" label="{{ __('common.phone_number') }}" value="{{ old('phone_number') }}" />
                        </div>
                    </div>
                    <div class="grid gap-4">
                        <h4 class="text-xl font-semibold text-slate-700">{{ __('common.billing_information') }}</h4>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <x-client::input type="text" name="company_name" label="{{ __('common.company_name') }}" value="{{ old('company_name') }}" required />
                            <x-client::input type="text" name="street_address_1" label="{{ __('common.street_address_1') }}" value="{{ old('street_address_1') }}" required />
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <x-client::input type="text" name="street_address_2" label="{{ __('common.street_address_2') }}" value="{{ old('street_address_2') }}" />
                            <x-client::input type="text" name="city" label="{{ __('common.city') }}" value="{{ old('city') }}" required />
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <x-client::input type="text" name="state" label="{{ __('common.state') }}" value="{{ old('state') }}" required />
                            <x-client::input type="number" name="postcode" label="{{ __('common.postcode') }}" value="{{ old('postcode') }}" required />
                        </div>
                        <x-client::select name="country" label="{{ __('common.country') }}" required>
                            @foreach (config('utils.countries') as $country => $label)
                                <option value="{{ $country }}"
                                    {{ old('country') == $country ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </x-client::select>
                    </div>
                    <div class="grid gap-4">
                        <h4 class="text-xl font-semibold text-slate-700">{{ __('common.security_information') }}</h4>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <x-client::input type="password" name="password" label="{{ __('common.password') }}" required />
                            <x-client::input type="password" name="password_confirmation" label="{{ __('common.confirm_password') }}" required />
                        </div>
                    </div>
                    <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.sign_up') }}</button>
                    <span class="text-slate-600">{{ __('auth.have_account') }} <a href="{{ route('client.login') }}" class="text-billmora-primary hover:text-billmora-primary-hover font-semibold">{{ __('common.sign_in') }}</a></span>
                </div>
            </div>
        </form>
        <div class="sticky top-0 w-auto max-w-180 lg:max-w-none lg:w-1/2 h-auto lg:h-dvh lg:flex justify-center bg-billmora-primary mx-auto rounded-2xl lg:rounded-none lg:rounded-bl-[100px]">
            <div class="max-w-180 my-8 lg:my-auto mx-8 space-y-6">
                <img src="https://media.billmora.com/logo/main-invert-bgnone.png" alt="brand logo" class="w-auto h-32">
                <span class="text-2xl md:text-3xl lg:text-4xl font-bold text-white">Grow your business with Billmora!</span>
                <p class="text-slate-200">Billmora is a free and open-source billing management platform designed to automate recurring services and simplify operations for hosting businesses.</p>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>