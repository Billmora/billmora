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
  <div class="flex flex-col gap-4 bg-billmora-2 w-full p-6 rounded-xl border-4 border-billmora-3">
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl text-center text-slate-700 font-bold">{{ __('client.2fa_backup_title') }}</h2>
      <p class="text-slate-500">{{ __('client.2fa_backup_description') }}</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 text-center mt-4">
      @foreach ($codes as $code)
        <span class="text-xl font-semibold text-slate-700">{{ $code }}</span>
      @endforeach
    </div>
    <div class="flex gap-2 ml-auto mt-6">
      <form action="{{ route('client.two-factor.backup.download') }}" method="POST">
        @csrf
        <x-client::button type="submit" class="font-semibold">{{ __('common.download') }}</x-client::button>
      </form>
      <form action="{{ route('client.two-factor.backup.store') }}" method="POST">
        @csrf
        <x-client::button type="submit" variant="secondary" class="font-semibold">{{ __('common.continue') }}</x-client::button>
      </form>
    </div>
  </div>
</div>
@endsection