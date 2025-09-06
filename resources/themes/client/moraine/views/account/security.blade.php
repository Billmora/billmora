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
    </div>
</div>
@endsection