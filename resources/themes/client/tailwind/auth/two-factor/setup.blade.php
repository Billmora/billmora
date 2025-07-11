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
  <form action="{{ route('client.two-factor.setup.store') }}" method="POST" class="flex flex-col gap-4 bg-billmora-2 w-full p-6 rounded-xl border-4 border-billmora-3">
    @csrf
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl text-center text-slate-700 font-bold">{{ __('client.2fa_setup_title') }}</h2>
      <p class="text-slate-500">{{ __('client.2fa_setup_description') }}</p>
    </div>
    <div class="mx-auto text-center">
      {!! $qrCode !!}
      <span class="font-bold text-slate-800">{{ $secretKey }}</span>
    </div>
    <div class="flex flex-col gap-2">
      <span class="font-semibold text-slate-600">{{ __('auth.2fa_step_1') }}</span>
      <span class="font-semibold text-slate-600">{{ __('auth.2fa_step_2') }}</span>
      <span class="font-semibold text-slate-600">{{ __('auth.2fa_step_3') }}</span>
    </div>
    <x-client::input type="text" name="totp" label="{{ __('client.2fa_totp') }}" required/>
    <div class="flex gap-2 ml-auto mt-6">
      <x-client::link href="/account/security" variant="secondary" class="font-semibold">{{ __('common.cancel') }}</x-client::link>
      <x-client::button type="submit" class="font-semibold">{{ __('common.continue') }}</x-client::button>
    </div>
  </form>
</div>
@endsection