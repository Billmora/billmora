<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('client::layouts.meta')
</head>
<body class="bg-white">
    <div class="flex flex-col lg:flex-row w-full min-h-dvh">
        <div class="w-full lg:w-1/2 h-auto p-8">
            <div class="max-w-170 h-full flex flex-col justify-between mx-auto">
                <div class="flex flex-col gap-6 my-auto">
                    @if (session('success'))
                        <x-client::alert variant="success">{{ session('success') }}</x-client::alert>
                    @endif
                    @if (session('error'))
                        <x-client::alert variant="danger">{{ session('error') }}</x-client::alert>
                    @endif
                    <form action="{{ route('client.two-factor.setup.store') }}" method="POST" class="flex flex-col gap-4 bg-white w-full p-8 rounded-xl border-2 border-billmora-2">
                      @csrf
                      <div class="flex flex-col gap-2">
                        <h2 class="text-xl text-center text-slate-700 font-bold">{{ __('auth.2fa.setup.title') }}</h2>
                        <p class="text-slate-500">{{ __('auth.2fa.setup.description') }}</p>
                      </div>
                      <div class="mx-auto text-center">
                        {!! $qrCode !!}
                        <span class="font-bold text-slate-800">{{ $secretKey }}</span>
                      </div>
                      <div class="flex flex-col gap-2">
                        <span class="font-semibold text-slate-600">{{ __('auth.2fa.setup.step_1') }}</span>
                        <span class="font-semibold text-slate-600">{{ __('auth.2fa.setup.step_2') }}</span>
                        <span class="font-semibold text-slate-600">{{ __('auth.2fa.setup.step_3') }}</span>
                      </div>
                      <x-client::input type="text" name="totp" label="{{ __('auth.2fa.verify.totp') }}" autocomplete="off" required/>
                      <div class="flex gap-2 ml-auto mt-6">
                        <a href="{{ route('client.account.security') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
                        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.continue') }}</button>
                      </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="w-auto max-w-170 lg:max-w-none lg:w-1/2 h-auto lg:flex justify-center bg-billmora-primary m-8 lg:m-0 mx-8 sm:mx-auto rounded-2xl lg:rounded-none lg:rounded-bl-[100px]">
            <div class="max-w-170 my-8 lg:my-auto mx-8 space-y-6">
                <img src="https://media.billmora.com/logo/main-invert-bgnone.png" alt="brand logo" class="w-auto h-32">
                <span class="text-2xl md:text-3xl lg:text-4xl font-bold text-white">Grow your business with Billmora!</span>
                <p class="text-slate-200">Billmora is a free and open-source billing management platform designed to automate recurring services and simplify operations for hosting businesses.</p>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>