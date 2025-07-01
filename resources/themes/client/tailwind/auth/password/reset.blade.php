@extends('client::layouts.app')

@section('body')
@if(session('success'))
  <div class="max-w-[40rem] flex mx-auto mb-6">
    <x-client::alert variant="success" icon="lucide-badge-check" description="{{ session('success') }}" />
  </div>
@endif
@if(session('error'))
  <div class="max-w-[40rem] flex mx-auto mb-6">
    <x-client::alert variant="danger" icon="lucide-triangle-alert" description="{{ session('error') }}" />
  </div>
@endif
<div class="max-w-[30rem] flex m-auto">
  <form action="{{ route('client.password.reset.store') }}" method="POST" class="flex flex-col bg-billmora-2 w-full p-6 rounded-xl border-4 border-billmora-3">
    @csrf
    <h2 class="text-2xl text-center text-slate-700 font-bold mb-6">{{ __('auth.reset_password_title') }}</h2>
    <input name="token" id="token" type="text" value="{{ $token }}" hidden>
    <div class="flex flex-col gap-2">
      <x-client::input type="email" name="email" label="{{ __('client.email') }}" value="{{ $email }}" readonly required/>
      <x-client::input type="password" name="password" label="{{ __('client.password') }}" required/>
      <x-client::input type="password" name="password_confirmation" label="{{ __('client.confirm_password') }}" required/>
    </div>
    <x-client::button type="submit" class="justify-center mt-6 font-semibold">{{ __('auth.confirm_update') }}</x-client::button>
  </form>
</div>
@endsection