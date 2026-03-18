@extends('client::layouts.app')

@section('title', 'Account Security')

@section('body')
<div class="grid gap-5">
    @error('totp')
        <x-client::alert variant="danger" title="{{ $message }}" />
    @enderror
    <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-3 gap-5">
        <form action="{{ route('client.account.security.email.update') }}" method="POST" class="w-full h-fit grid gap-4 bg-billmora-bg p-8 border-2 border-billmora-2 rounded-xl">
            @csrf
            @method('PUT')
            <h3 class="text-xl font-semibold text-slate-700">{{ __('client/account.update_email') }} </h3>
            <x-client::input type="email" name="new_email" label="{{ __('client/account.new_email') }}" value="{{ old('new_email', $user->email) }}" required />
            <x-client::input type="password" name="confirm_password" label="{{ __('common.confirm_password') }}" required />
            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.update') }}</button>
        </form>
        <form action="{{ route('client.account.security.password.update') }}" method="POST" class="w-full h-fit grid gap-4 bg-billmora-bg p-8 border-2 border-billmora-2 rounded-xl">
            @csrf
            @method('PUT')
            <h3 class="text-xl font-semibold text-slate-700">{{ __('client/account.update_password') }} </h3>
            <x-client::input type="password" name="current_password" label="{{ __('client/account.current_password') }}" required />
            <x-client::input type="password" name="new_password" label="{{ __('client/account.new_password') }}" required />
            <x-client::input type="password" name="new_password_confirmation" label="{{ __('client/account.confirm_new_password') }}" required />
            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.update') }}</button>
        </form>
        <div class="w-full h-fit grid gap-4 bg-billmora-bg p-8 border-2 border-billmora-2 rounded-xl">
            <h3 class="text-xl font-semibold text-slate-700">{{ __('auth.2fa.title') }} </h3>
            <p class="text-slate-500">{{ __('auth.2fa.description') }}</p>
            @if ($user->twoFactor && $user->twoFactor->isActive())
                <x-client::modal.trigger modal="2faDisableModal" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.disable') }}</x-client::modal.trigger>
            @else
                <a href="{{ route('client.two-factor.setup') }}" variant="primary" icon="lucide-check" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.enable') }}</a>
            @endif
        </div>
    </div>
    <div class="w-full bg-billmora-bg p-8 border-2 border-billmora-2 rounded-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-xl font-semibold text-slate-700">{{ __('client/account.sessions.title') }}</h3>
                <p class="text-slate-500 text-sm mt-1">{{ __('client/account.sessions.description') }}</p>
            </div>
            @if ($sessions->where('is_current', false)->count() > 0)
                <x-client::modal.trigger modal="revokeOtherSessionsModal" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 shrink-0 px-3 py-2 text-white text-sm font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('client/account.sessions.revoke_others') }}
                </x-client::modal.trigger>
            @endif
        </div>
        <div class="grid gap-3">
            @foreach ($sessions as $session)
                <div class="flex items-center gap-4 p-4 border-2 border-billmora-2 rounded-xl">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full shrink-0 bg-billmora-1 text-slate-500">
                        @if ($session->is_mobile)
                            <x-lucide-smartphone class="w-5 h-5" />
                        @else
                            <x-lucide-monitor class="w-5 h-5" />
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="font-semibold text-slate-700">{{ $session->browser }} on {{ $session->platform }}</span>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 text-sm text-slate-500 mt-1">
                            <span>{{ $session->ip_address }}</span>
                            <span class="hidden sm:inline">&middot;</span>
                            <span>{{ __('client/account.sessions.last_active', ['time' => $session->last_active->diffForHumans()]) }}</span>
                        </div>
                    </div>
                    <x-client::modal.trigger modal="revokeSessionModal-{{ $loop->index }}" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 shrink-0 px-3 py-2 text-white text-sm font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('client/account.sessions.revoke') }}
                    </x-client::modal.trigger>
                </div>
            @endforeach
        </div>
    </div>
</div>
<x-client::modal.content
    modal="2faDisableModal"
    variant="danger"
    size="lg"
    position="centered"
    title="{{ __('common.disable_modal_title') }}"
    description="{{ __('common.disable_modal_description', ['item' => __('auth.2fa.title')]) }}">
    <form action="{{ route('client.two-factor.disable') }}" method="POST">
        @csrf
        <x-input type="text" name="totp" label="{{ __('auth.2fa.verify.totp') }}" required />
        <div class="flex justify-end gap-2 mt-4">
            <x-client::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-client::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.disable') }}</button>
        </div>
    </form>
</x-client::modal.content>
@foreach ($sessions as $session)
    <x-client::modal.content
        modal="revokeSessionModal-{{ $loop->index }}"
        variant="danger"
        size="lg"
        position="centered"
        title="{{ __('common.confirm_modal_title') }}"
        description="{{ __('common.confirm_modal_description', ['item' => __('client/account.sessions.revoke')]) }}">
        <form action="{{ route('client.account.security.session.revoke', $session->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <p class="text-sm text-slate-500 mb-4">{{ $session->browser }} on {{ $session->platform }} &middot; {{ $session->ip_address }}</p>
            <div class="flex justify-end gap-2">
                <x-client::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-client::modal.trigger>
                <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('client/account.sessions.revoke') }}</button>
            </div>
        </form>
    </x-client::modal.content>
@endforeach
<x-client::modal.content
    modal="revokeOtherSessionsModal"
    variant="danger"
    size="lg"
    position="centered"
    title="{{ __('common.confirm_modal_title') }}"
    description="{{ __('common.confirm_modal_description', ['item' => __('client/account.sessions.revoke_others')]) }}">
    <form action="{{ route('client.account.security.sessions.revoke-others') }}" method="POST">
        @csrf
        @method('DELETE')
        <x-client::input type="password" name="confirm_password" label="{{ __('common.confirm_password') }}" required />
        <div class="flex justify-end gap-2 mt-4">
            <x-client::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-client::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('client/account.sessions.revoke_others') }}</button>
        </div>
    </form>
</x-client::modal.content>
@endsection