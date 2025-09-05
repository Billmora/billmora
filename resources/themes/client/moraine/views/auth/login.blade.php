<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('client::layouts.meta')
</head>
<body class="bg-white">
    <div class="flex flex-col lg:flex-row w-full min-h-dvh">
        <form action="{{ route('client.login.store') }}" method="POST" class="w-full lg:w-1/2 h-auto p-8">
            @csrf
            <div class="max-w-140 h-full flex flex-col justify-between mx-auto">
                <a href="#" class="flex gap-2 items-center mb-10 lg:mb-0 text-slate-500 font-semibold">
                    <x-lucide-chevron-left class="w-auto h-5" />
                    <span>{{ __('common.back_to', ['page' => __('common.page.portal')]) }}</span>
                </a>
                <div class="grid gap-6 my-auto">
                    @if (session('success'))
                        <x-client::alert variant="success">{{ session('success') }}</x-client::alert>
                    @endif
                    @if (session('warning'))
                        <x-client::alert variant="warning">{{ session('warning') }}</x-client::alert>
                    @endif
                    @if (session('error'))
                        <x-client::alert variant="danger">
                            {{ session('error') }}
                            @if (session('expired_token'))
                                <button type="button" onclick="document.getElementById('resendEmail').submit()" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.resend') }}</button>
                            @endif
                        </x-client::alert>
                    @endif
                    <h3 class="font-semibold text-2xl text-slate-700">{{ __('auth.page.login') }}</h3>
                    <div class="grid gap-3">
                        <x-client::input type="email" name="email" label="{{ __('common.email') }}" required />
                        <x-client::input type="password" name="password" label="{{ __('common.password') }}" required />
                        <a href="{{ route('client.password.forgot') }}" class="text-billmora-primary hover:text-billmora-primary-hover font-semibold text-end">{{ __('auth.forgot_password') }}</a>
                    </div>
                    <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.sign_in') }}</button>
                    <span class="text-slate-600">{{ __('auth.dont_have_account') }} <a href="{{ route('client.register') }}" class="text-billmora-primary hover:text-billmora-primary-hover font-semibold">{{ __('common.sign_up') }}</a></span>
                </div>
            </div>
        </form>
        <div class="w-auto max-w-140 lg:max-w-none lg:w-1/2 h-auto lg:flex justify-center bg-billmora-primary m-8 lg:m-0 mx-8 sm:mx-auto rounded-2xl lg:rounded-none lg:rounded-bl-[100px]">
            <div class="max-w-140 my-8 lg:my-auto mx-8 space-y-6">
                <img src="https://media.billmora.com/logo/main-invert-bgnone.png" alt="brand logo" class="w-auto h-32">
                <span class="text-2xl md:text-3xl lg:text-4xl font-bold text-white">Grow your business with Billmora!</span>
                <p class="text-slate-200">Billmora is a free and open-source billing management platform designed to automate recurring services and simplify operations for hosting businesses.</p>
            </div>
        </div>
        <form id="resendEmail" action="{{ route('client.email.resend') }}" method="POST">
            @csrf
            <input type="hidden" name="expired_token" value="{{ session('expired_token') }}">
        </form>
    </div>
    @livewireScripts
</body>
</html>