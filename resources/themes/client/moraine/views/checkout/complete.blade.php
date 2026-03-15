@extends('client::layouts.app')

@section('title', 'Checkout Complete')

@section('body')
<div class="grid justify-center gap-4 bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
    <div class="flex flex-col items-center text-center gap-2">
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
    <div class="grid gap-3 border-2 border-billmora-2 p-6 rounded-2xl mt-4 bg-slate-50">
        <h3 class="text-lg text-slate-700 font-bold border-b-2 border-slate-200 pb-3 mb-1">
            {{ __('client/checkout.order_summary') }}
        </h3>
        <div class="space-y-3">
            @foreach ($order->items as $item)
                <div class="flex justify-between items-start text-sm font-medium">
                    <div class="flex flex-col">
                        <span class="text-slate-600">
                            {{ $item->name }} 
                            @if($item->quantity > 1)
                                <span class="inline-block text-xs bg-billmora-2 text-billmora-primary-500 font-bold px-2 py-0.5 rounded-md ml-1">
                                    x{{ $item->quantity }}
                                </span>
                            @endif
                        </span>
                        <span class="text-slate-400 text-xs mt-0.5">{{ ucfirst($item->billing_type) }}</span>
                    </div>
                    <span class="text-slate-700 font-semibold">
                        {{ Currency::format(($item->price + $item->setup_fee) * $item->quantity, $order->currency) }}
                    </span>
                </div>
            @endforeach
        </div>
        <hr class="border-t-2 border-dashed border-slate-200 my-2">
        <div class="flex justify-between font-bold text-base mt-1 items-center">
            <span class="text-slate-600 uppercase tracking-wider text-sm">{{ __('client/checkout.total_due') }}</span>
            <span class="text-billmora-primary-500 text-xl">{{ Currency::format($order->total, $order->currency) }}</span>
        </div>
    </div>
    @if ($invoice?->status === 'unpaid')
        <div class="grid gap-4 bg-orange-100 border-2 border-orange-400 p-6 rounded-2xl">
            <span class="text-orange-500">{{ __('client/checkout.complete.unpaid_note') }}</span>
            <a href="{{ route('client.invoices.show', ['invoice' => $invoice->invoice_number]) }}" class="bg-orange-500 hover:bg-orange-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('client/checkout.complete.view_invoice') }}
            </a>
        </div>
    @endif
    <a href="{{ route('client.dashboard') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 mx-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
        {{ __('client/checkout.complete.back_to_client') }}
    </a>
</div>
@endsection