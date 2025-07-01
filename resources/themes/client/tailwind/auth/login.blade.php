@extends('client::layouts.app')

@section('body')
@if(session('success'))
  <div class="max-w-[40rem] flex mx-auto mb-6">
    <x-client::alert variant="success" icon="lucide-badge-check" description="{{ session('success') }}" />
  </div>
@endif
@if(session('error'))
<div class="max-w-[40rem] flex mx-auto mb-6">
  <x-client::alert variant="danger" icon="lucide-triangle-alert" description="{{ session('error') }}" >
    @if(session('resend_token'))
      <form action="{{ route('client.email.resend') }}" method="POST" class="ml-auto">
        @csrf
        <input type="hidden" name="resend_token" value="{{ session('resend_token') }}">
        <x-client::button type="submit" class="font-semibold bg-red-500 hover:bg-red-600">{{ __('auth.resend_verification') }}</x-client::button>
      </form>
    @endif
  </x-client::alert>
</div>
@enderror
<div class="max-w-[30rem] flex m-auto">
  <form action="{{ route('client.login.store') }}" method="POST" class="flex flex-col bg-billmora-2 w-full p-6 rounded-xl border-4 border-billmora-3">
    @csrf
    <h2 class="text-2xl text-center text-slate-700 font-bold mb-6">{{ __('auth.login_title') }}</h2>
    <div class="flex flex-col gap-2">
      <x-client::input type="email" name="email" label="{{ __('client.email') }}" required/>
      <div class="relative">
        <x-client::input type="password" name="password" label="{{ __('client.password') }}" required/>
        <x-client::link href="/auth/password/forgot" class="absolute right-0 top-0 font-semibold">{{ __('auth.forgot_password') }}</x-client::link>
      </div>
    </div>
    <x-client::button type="submit" class="justify-center mt-6 font-semibold">{{ __('common.sign_in') }}</x-client::button>
    <p class="flex gap-1 text-slate-700 mt-4">{{ __('auth.dont_have_account') }} <x-client::link href="/auth/register" class="font-semibold">{{ __('common.sign_up') }}</x-client::link></p>
  </form>
</div>
@endsection