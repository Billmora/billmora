@extends('client::layouts.app')

@section('title', __('client/affiliate.join_title'))

@section('body')
<div class="flex flex-col gap-6">
    <div class="grid gap-1">
        <h1 class="text-2xl font-bold text-slate-700">{{ __('client/affiliate.title') }}</h1>
        <p class="text-slate-500 text-sm">{{ __('client/affiliate.join_subtitle') }}</p>
    </div>

    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-12 flex flex-col items-center justify-center text-center gap-5">
        <div class="bg-billmora-primary-500/10 p-5 rounded-full text-billmora-primary-500">
            <x-lucide-handshake class="w-12 h-12" />
        </div>
        <div class="grid gap-2 max-w-md">
            <h2 class="text-xl font-bold text-slate-700">{{ __('client/affiliate.join_box_title') }}</h2>
            <p class="text-slate-500">{{ __('client/affiliate.join_box_description') }}</p>
        </div>
        <form action="{{ route('client.modules.affiliate.join') }}" method="POST">
            @csrf
            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-3 text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('client/affiliate.join_now') }}
            </button>
        </form>
    </div>
</div>
@endsection
