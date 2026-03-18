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
                    @if (Billmora::getAuth('oauth_google_enabled') || Billmora::getAuth('oauth_discord_enabled') || Billmora::getAuth('oauth_github_enabled'))
                        <div class="flex items-center gap-4">
                            <div class="flex-1 h-px bg-slate-200"></div>
                            <span class="text-sm text-slate-400">{{ __('auth.oauth.or_continue_with') }}</span>
                            <div class="flex-1 h-px bg-slate-200"></div>
                        </div>
                        <div class="flex gap-3">
                            @if (Billmora::getAuth('oauth_google_enabled'))
                                <a href="{{ route('client.oauth.redirect', ['provider' => 'google']) }}" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 border-2 border-billmora-2 rounded-lg text-slate-700 hover:bg-slate-50 transition duration-150">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                                    <span class="text-sm font-semibold">Google</span>
                                </a>
                            @endif
                            @if (Billmora::getAuth('oauth_discord_enabled'))
                                <a href="{{ route('client.oauth.redirect', ['provider' => 'discord']) }}" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 border-2 border-billmora-2 rounded-lg text-slate-700 hover:bg-slate-50 transition duration-150">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#5865F2"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.095 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.095 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                                    <span class="text-sm font-semibold">Discord</span>
                                </a>
                            @endif
                            @if (Billmora::getAuth('oauth_github_enabled'))
                                <a href="{{ route('client.oauth.redirect', ['provider' => 'github']) }}" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 border-2 border-billmora-2 rounded-lg text-slate-700 hover:bg-slate-50 transition duration-150">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                                    <span class="text-sm font-semibold">GitHub</span>
                                </a>
                            @endif
                        </div>
                    @endif
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