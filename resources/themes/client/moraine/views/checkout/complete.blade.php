@extends('client::layouts.app')

@section('title', 'Checkout Complete')

@section('body')
<div class="grid justify-center gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
    <div class="flex flex-col items-center gap-2">
        <div class="bg-green-200 p-4 rounded-full">
            <x-lucide-check class="w-auto h-8 text-green-500" />
        </div>
        <h2 class="text-2xl text-green-500 font-semibold">
            {{ __('client/checkout.complete.heading') }}
        </h2>
    </div>
    <div class="flex flex-col items-center text-center">
        <p class="text-slate-500">{{ __('client/checkout.complete.message') }}</p>
        <p class="text-slate-500">{{ __('client/checkout.complete.information', ['order_number' => $order->order_number]) }}</p>
    </div>
    <div class="grid gap-2 border-2 border-billmora-2 p-6 rounded-2xl">
        <h3 class="text-lg text-slate-600 font-semibold">{{ __('client/checkout.order_summary') }}</h3>
        <div class="flex justify-between font-medium">
            <span class="text-slate-500">{{ $order->package->name }} - {{ $order->package->catalog->name }}</span>
            <span class="text-slate-500">{{ Currency::format($order->total, $order->currency) }}</span>
        </div>
    </div>
    @if ($invoice?->status === 'unpaid')
        <div class="grid gap-2 bg-orange-100 border-2 border-orange-400 p-6 rounded-2xl">
            <span class="text-orange-500">{{ __('client/checkout.complete.unpaid_note') }}</span>
            <a href="{{ route('client.invoices.show', ['invoice' => $invoice->invoice_number]) }}" class="bg-orange-400 hover:bg-orange-300 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('client/checkout.complete.view_invoice') }}
            </a>
        </div>
    @endif
    <a href="{{ route('client.dashboard') }}" class="bg-billmora-primary hover:bg-billmora-primary-hover mx-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
        {{ __('client/checkout.complete.back_to_client') }}
    </a>
</div>
@endsection