@section('title', 'Login')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('client::layouts.meta')
</head>
<body class="bg-billmora-bg">
    <div class="flex flex-col lg:flex-row-reverse w-full min-h-dvh">
        <form action="{{ route('client.login.store') }}" method="POST" class="w-full lg:w-1/2 h-auto p-8">
            @csrf
            <div class="max-w-140 h-full flex flex-col justify-between mx-auto">
                <a href="{{ route('portal.home') }}" class="flex gap-2 items-center mb-10 lg:mb-0 text-slate-500 font-semibold">
                    <x-lucide-chevron-left class="w-auto h-5" />
                    <span>{{ __('common.back_to', ['page' => __('common.page.portal')]) }}</span>
                </a>
                <div class="grid gap-6 my-auto">
                    @if (session('success'))
                        <x-client::alert variant="success" title="{{ session('success') }}" />
                    @endif
                    @if (session('warning'))
                        <x-client::alert variant="warning" title="{{ session('warning') }}" />
                    @endif
                    @if (session('error'))
                        <x-client::alert variant="danger" title="{{ session('error') }}">
                            @if (session('email_token'))
                                <button type="button" onclick="document.getElementById('resendEmail').submit()" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.resend') }}</button>
                            @endif
                        </x-client::alert>
                    @endif
                    <h3 class="font-semibold text-2xl text-slate-700">{{ __('auth.page.login') }}</h3>
                    <div class="grid gap-3">
                        <x-client::input type="email" name="email" label="{{ __('common.email') }}" required />
                        <x-client::input type="password" name="password" label="{{ __('common.password') }}" required />
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" class="w-4 h-4 accent-billmora-primary-500 text-red border-2 outline-none focus:ring-2 ring-billmora-primary-500 cursor-pointer">
                                <span class="text-sm text-slate-600">{{ __('auth.remember_me') }}</span>
                            </label>
                            <a href="{{ route('client.password.forgot') }}" class="text-billmora-primary-500 hover:text-billmora-primary-600 font-semibold">{{ __('auth.forgot_password') }}</a>
                        </div>
                        <x-client::captcha form="login_form" class="mx-auto" />
                    </div>
                    <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.sign_in') }}</button>
                    <span class="text-slate-600">{{ __('auth.dont_have_account') }} <a href="{{ route('client.register') }}" class="text-billmora-primary-500 hover:text-billmora-primary-600 font-semibold">{{ __('common.sign_up') }}</a></span>
                </div>
            </div>
        </form>
        <div class="w-auto max-w-140 lg:max-w-none lg:w-1/2 h-auto lg:flex justify-center bg-billmora-primary-500 m-8 lg:m-0 mx-8 sm:mx-auto rounded-2xl lg:rounded-none lg:rounded-br-[100px]">
            <div class="max-w-140 my-8 lg:my-auto mx-8 space-y-6">
                <img src="{{ $clientThemeConfig['auth_logo_url'] }}" alt="brand logo" class="w-auto h-32">
                <span class="text-2xl md:text-3xl lg:text-4xl font-bold text-white">{{ $clientThemeConfig['auth_message_title'] }}</span>
                <p class="text-slate-200">{{ $clientThemeConfig['auth_message_description'] }}</p>
            </div>
        </div>
    </div>
    <form id="resendEmail" action="{{ route('client.email.resend') }}" method="POST">
        @csrf
        <input type="hidden" name="email_token" value="{{ session('email_token') }}">
    </form>
    @livewireScripts
</body>
</html>