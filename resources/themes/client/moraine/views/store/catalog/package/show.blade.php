@extends('client::layouts.app')

@section('title', 'Store')

@section('body')
<form action="" method="POST" class="flex flex-col lg:flex-row gap-5">
    @csrf
    <div class="w-full lg:w-2/3 h-fit grid gap-4">
        <div class="bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <h1 class="text-xl font-semibold text-slate-600">{{ $package->catalog->name }} - {{ $package->name }}</h1>
            <p class="text-slate-500">
                {!! $package->description !!}
            </p>
        </div>
        <span class="text-xl text-slate-600 font-semibold">{{ __('client/store.package.billing_cycle') }}</span>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($prices as $price)
                <label class="group relative cursor-pointer">
                    <input
                        type="radio"
                        name="price_id"
                        value="{{ $price->id }}"
                        class="hidden"
                    >
                    <div class="h-full bg-white p-4 border-2 border-billmora-2
                            rounded-xl transition-all
                            group-has-[:checked]:border-billmora-primary
                            hover:border-billmora-primary"
                    >
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-4 w-4 rounded-full border-2 border-slate-500
                                    group-has-[:checked]:border-billmora-primary
                                    group-has-[:checked]:bg-billmora-primary
                                    transition-all"
                            ></div>
                            <div class="flex flex-col">
                                <h4 class="text-sm font-semibold text-slate-600">
                                    {{ $price->name }}
                                </h4>
                                <span class="text-sm font-semibold text-slate-500">
                                    @if ($price->type === 'free')
                                        Free
                                    @else
                                        {{ Currency::format($price->rates[$currencyActive['code']]['price']) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
    </div>
    <div class="w-full lg:w-1/3 h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl space-y-4">
        <h2 class="text-xl font-semibold text-slate-600 mb-4">
            {{ __('client/store.package.order_summary') }}
        </h2>
        <div class="grid">
            <div class="flex gap-3 justify-between">
                <span class="text-slate-600 font-semibold text-start break-all">{{ $package->catalog->name }} - {{ $package->name }}</span>
                <span class="text-slate-600 font-semibold text-end break-all">5.00</span>
            </div>
            {{-- TODO: will be replace with product variant --}}
            <div class="flex gap-3 justify-between">
                <span class="text-slate-500 font-semibold text-start break-all">VariantName: example</span>
                <span class="text-slate-500 font-semibold text-end break-all">$100.00 USD</span>
            </div>
            <hr class="border-t-2 border-billmora-2 my-4">
            <div class="flex gap-3 justify-between">
                <span class="text-slate-500 font-semibold text-start break-all">{{ __('client/store.package.setup_fee') }}</span>
                <span class="text-slate-500 font-semibold text-end break-all">$100.00 USD</span>
            </div>
            <hr class="border-t-2 border-billmora-2 my-4">
            <div class="flex flex-col">
                <span class="text-slate-600 font-semibold break-all">{{ __('client/store.package.due_today') }}</span>
                <span class="text-xl text-slate-600 font-semibold break-all">$100.00 USD</span>
            </div>
        </div>
        <button type="submit" 
                class="w-full bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white 
                    rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('client/store.package.checkout') }}
        </button>
    </div>
</form>
@endsection