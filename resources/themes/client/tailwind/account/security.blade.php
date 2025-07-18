@extends('client::layouts.app')

@section('body')
@if(session('success'))
  <div class="w-full flex mx-auto mb-6">
    <x-client::alert variant="success" icon="lucide-badge-check" description="{{ session('success') }}" />
  </div>
@endif
@if(session('error'))
  <div class="w-full flex mx-auto mb-6">
    <x-client::alert variant="danger" icon="lucide-triangle-alert" description="{{ session('error') }}" />
  </div>
@endif
@error('totp')
  <div class="w-full flex mx-auto mb-6">
    <x-client::alert variant="danger" icon="lucide-triangle-alert" description="{{ $message }}" />
  </div>
@enderror
<x-client::modal modal="2faDisableModal" title="{{ __('client.2fa_title') }}" description="{{ __('common.disable_alert', ['title' => __('client.2fa_title')]) }}"  icon="lucide-shield-alert" variant="danger" position="centered">
  <form action="{{ route('client.two-factor.disable') }}" method="POST">
    @csrf
    <x-input type="text" name="totp" label="{{ __('client.2fa_totp') }}" required />
    <div class="flex justify-end gap-2 mt-4">
      <x-button type="button" x-on:click="$store.modal.close()" variant="secondary">{{ __('common.cancel') }}</x-button>
      <x-button type="submit" variant="danger">{{ __('common.disable') }}</x-button>
    </div>
  </form>
</x-client::modal>
<div class="flex flex-col md:grid md:grid-cols-2 md:gap-4 lg:grid-cols-3">
  <form action="{{ route('client.account.security.password.update') }}" method="POST" class="w-full h-h-fit bg-billmora-2 p-6 rounded-lg border-3 border-billmora-3">
    @csrf
    <div class="flex flex-col gap-4">
      <h3 class="text-xl text-slate-600 font-bold">{{ __('client.update_password') }}</h3>
      <div class="space-y-2">
        <x-client::input type="password" name="current_password" label="{{ __('client.current_password') }}" required />
        <x-client::input type="password" name="new_password" label="{{ __('client.new_password') }}" required />
        <x-client::input type="password" name="new_password_confirmation" label="{{ __('client.confirm_new_password') }}" required />
      </div>
      <x-client::button icon="lucide-save" class="ml-auto font-semibold">{{ __('common.save') }}</x-client::button>
    </div>
  </form>
  <form action="{{ route('client.account.security.email.update') }}" method="POST" class="w-full h-fit bg-billmora-2 p-6 rounded-lg border-3 border-billmora-3">
    @csrf
    <div class="flex flex-col gap-4">
      <h3 class="text-xl text-slate-600 font-bold">{{ __('client.update_email') }}</h3>
      <div class="space-y-2">
        <x-client::input type="email" name="new_email" label="{{ __('client.new_email') }}" required />
        <x-client::input type="password" name="password" label="{{ __('client.confirm_password') }}" required />
      </div>
      <x-client::button icon="lucide-save" class="ml-auto font-semibold">{{ __('common.save') }}</x-client::button>
    </div>
  </form>
  <div class="w-full h-fit bg-billmora-2 p-6 rounded-lg border-3 border-billmora-3">
    <div class="flex flex-col gap-4">
      <h3 class="text-xl text-slate-600 font-bold">{{ __('client.2fa_title') }}</h3>
      <div class="space-y-2">
        <p class="text-slate-500">{{ __('client.2fa_description') }}</p>
        @if (auth()->user()->twoFactor && auth()->user()->twoFactor->enabled)
          <x-client::button modal="2faDisableModal" variant="danger" icon="lucide-x" class="w-fit ml-auto font-semibold">{{ __('common.disable') }}</x-client::button>
        @else
          <x-client::link href="/auth/two-factor/setup" variant="primary" icon="lucide-check" class="w-fit ml-auto font-semibold">{{ __('common.enable') }}</x-client::link>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection