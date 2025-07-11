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
  <form action="{{ route('client.two-factor.verify.store') }}" method="POST" class="flex flex-col gap-4 bg-billmora-2 w-full p-6 rounded-xl border-4 border-billmora-3">
    @csrf
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl text-center text-slate-700 font-bold">{{ __('client.2fa_verify_title') }}</h2>
      <p class="text-slate-500">{{ __('client.2fa_verify_description') }}</p>
    </div>
    <div class="mt-4 space-y-2">
      <x-client::input type="text" name="totp" label="{{ __('client.2fa_totp') }}" required />
      <x-client::link href="/auth/two-factor/recovery" class="font-semibold">{{ __('client.2fa_lost_access') }}</x-client::link>
    </div>
    <div class="flex gap-2 ml-auto mt-6">
      <x-client::button type="submit" variant="secondary" class="font-semibold">{{ __('common.continue') }}</x-client::button>
    </div>
  </form>
</div>
@endsection