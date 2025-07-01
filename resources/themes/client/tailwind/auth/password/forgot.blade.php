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
  <form action="{{ route('client.password.forgot.store') }}" method="POST" class="flex flex-col bg-billmora-2 w-full p-6 rounded-xl border-4 border-billmora-3">
    @csrf
    <h2 class="text-2xl text-center text-slate-700 font-bold mb-6">{{ __('auth.forgot_password_title') }}</h2>
    <x-client::input type="email" name="email" label="{{ __('client.email') }}" required/>
    <x-client::button type="submit" class="justify-center mt-6 font-semibold">{{ __('auth.send_request') }}</x-client::button>
    <p class="flex gap-1 text-slate-700 mt-4">{{ __('auth.remembered_password') }} <x-client::link href="/auth/login" class="font-semibold">{{ __('common.sign_in') }}</x-client::link></p>
  </form>
</div>
@endsection