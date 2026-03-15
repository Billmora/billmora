@section('title', 'Two-Factor Recovery')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('client::layouts.meta')
</head>
<body class="bg-billmora-bg">
    <div class="flex flex-col lg:flex-row-reverse w-full min-h-dvh">
        <div class="w-full lg:w-1/2 h-auto p-8">
            <div class="max-w-170 h-full flex flex-col justify-between mx-auto">
                <div class="flex flex-col gap-6 my-auto">
                    @if (session('success'))
                        <x-client::alert variant="success" title="{{ session('success') }}" />
                    @endif
                    @if (session('error'))
                        <x-client::alert variant="danger" title="{{ session('error') }}" />
                    @endif
                    <form action="{{ route('client.two-factor.recovery.store') }}" method="POST" class="flex flex-col gap-4 bg-billmora-bg w-full p-8 rounded-xl border-2 border-billmora-2">
                        @csrf
                        <div class="flex flex-col gap-2">
                            <h2 class="text-xl text-center text-slate-700 font-bold">{{ __('auth.2fa.recovery.title') }}</h2>
                            <p class="text-slate-500">{{ __('auth.2fa.recovery.description') }}</p>
                        </div>
                        <div class="mt-4 space-y-2">
                            <x-client::input type="text" name="recovery_code" label="{{ __('auth.2fa.backup.recovery_code') }}" autocomplete="off" required />
                            <a href="{{ route('client.two-factor.verify') }}" class="text-billmora-primary-500 hover:text-billmora-primary-600 font-semibold">{{ __('auth.2fa.recovery.have_access') }}</a>
                        </div>
                        <div class="flex gap-2 ml-auto mt-6">
                            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.continue') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="w-auto max-w-170 lg:max-w-none lg:w-1/2 h-auto lg:flex justify-center bg-billmora-primary-500 m-8 lg:m-0 mx-8 sm:mx-auto rounded-2xl lg:rounded-none lg:rounded-br-[100px]">
            <div class="max-w-170 my-8 lg:my-auto mx-8 space-y-6">
                <img src="{{ $clientThemeConfig['auth_logo_url'] }}" alt="brand logo" class="w-auto h-32">
                <span class="text-2xl md:text-3xl lg:text-4xl font-bold text-white">{{ $clientThemeConfig['auth_message_title'] }}</span>
                <p class="text-slate-200">{{ $clientThemeConfig['auth_message_description'] }}</p>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>