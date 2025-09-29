@extends('client::layouts.app')

@section('body')
<div class="grid gap-5">
    @if (session('success'))
        <x-client::alert variant="success">{{ session('success') }}</x-client::alert>
    @endif
    @error('totp')
    <x-client::alert variant="danger">{{ $message }}</x-client::alert>
    @enderror
    @if (session('error'))
        <x-client::alert variant="danger">{{ session('error') }}</x-client::alert>
    @endif
    <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-3 gap-5">
        <form action="{{ route('client.account.security.email.update') }}" method="POST" class="w-full h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-xl">
            @csrf
            @method('PUT')
            <h3 class="text-xl font-semibold text-slate-700">{{ __('client/account.update_email') }} </h3>
            <x-client::input type="email" name="new_email" label="{{ __('client/account.new_email') }}" value="{{ old('new_email', $user->email) }}" required />
            <x-client::input type="password" name="confirm_password" label="{{ __('common.confirm_password') }}" required />
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.update') }}</button>
        </form>
        <form action="{{ route('client.account.security.password.update') }}" method="POST" class="w-full h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-xl">
            @csrf
            @method('PUT')
            <h3 class="text-xl font-semibold text-slate-700">{{ __('client/account.update_password') }} </h3>
            <x-client::input type="password" name="current_password" label="{{ __('client/account.current_password') }}" required />
            <x-client::input type="password" name="new_password" label="{{ __('client/account.new_password') }}" required />
            <x-client::input type="password" name="new_password_confirmation" label="{{ __('client/account.confirm_new_password') }}" required />
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.update') }}</button>
        </form>
        <div class="w-full h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-xl">
            <h3 class="text-xl font-semibold text-slate-700">{{ __('auth.2fa.title') }} </h3>
            <p class="text-slate-500">{{ __('auth.2fa.description') }}</p>
            @if ($user->twoFactor && $user->twoFactor->isActive())
                <x-client::modal.trigger modal="2faDisableModal" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.disable') }}</x-client::modal.trigger>
            @else
                <a href="{{ route('client.two-factor.setup') }}" variant="primary" icon="lucide-check" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.enable') }}</a>
            @endif
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
            <x-client::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-client::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.disable') }}</button>
        </div>
    </form>
</x-client::modal.content>
@endsection