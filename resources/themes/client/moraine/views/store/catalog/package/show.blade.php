@extends('client::layouts.app')

@section('title', 'Store')

@section('body')
<div x-data="orderSummary()" x-init="init()">
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
                @foreach ($prices as $index => $price)
                    @php
                        $isFree = $price->type === 'free';

                        $priceValue = $isFree ? 0 : ($price->rates[$currencyActive['code']]['price'] ?? 0);
                        $formattedPrice = $isFree ? 'Free' : Currency::format($priceValue);
                        
                        $setupFee = $isFree ? 0 : ($price->rates[$currencyActive['code']]['setup_fee'] ?? 0);
                        $formattedSetupFee = Currency::format($setupFee);
                        
                        $total = $priceValue + $setupFee;
                        $formattedTotal = Currency::format($total);
                    @endphp
                    <label class="group relative cursor-pointer">
                        <input
                            type="radio"
                            name="price_id"
                            value="{{ $price->id }}"
                            x-on:change="updateOrderSummary(
                                '{{ $price->name }}',
                                {{ $priceValue }},
                                {{ $setupFee }},
                                '{{ $formattedPrice }}',
                                '{{ $formattedSetupFee }}',
                                '{{ $formattedTotal }}'
                            )"
                            class="hidden"
                            {{ $loop->first ? 'checked' : '' }}
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
                                        {{ $formattedPrice }}
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
                    <span class="text-slate-600 font-semibold text-start break-all">
                        {{ $package->catalog->name }} - {{ $package->name }}
                    </span>
                    <span class="text-slate-600 font-semibold text-end break-all">
                        <span x-text="cyclePriceFormatted"></span>
                    </span>
                </div>
                {{-- TODO: will be replace with product variant --}}
                {{-- <div class="flex gap-3 justify-between">
                    <span class="text-slate-500 font-semibold text-start break-all">VariantName: example</span>
                    <span class="text-slate-500 font-semibold text-end break-all">$100.00 USD</span>
                </div> --}}
                <hr class="border-t-2 border-billmora-2 my-4">
                <span class="text-slate-600 font-semibold text-start text-md break-all">
                    {{ __('client/store.package.billing_cycle') }}
                </span>
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-500 font-semibold text-start break-all" x-text="cycleName"></span>
                    <span class="text-slate-500 font-semibold text-end break-all" x-text="cyclePriceFormatted"></span>
                </div>
                <hr class="border-t-2 border-billmora-2 my-4">
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-500 font-semibold text-start break-all">
                        {{ __('client/store.package.setup_fee') }}
                    </span>
                    <span class="text-slate-500 font-semibold text-end break-all" x-text="setupFeeFormatted"></span>
                </div>
                <hr class="border-t-2 border-billmora-2 my-4">
                <div class="flex flex-col">
                    <span class="text-slate-600 font-semibold break-all">
                        {{ __('client/store.package.due_today') }}
                    </span>
                    <span class="text-xl text-slate-600 font-semibold break-all" x-text="totalFormatted"></span>
                </div>
            </div>
            <button type="submit" 
                    class="w-full bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white 
                        rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('client/store.package.checkout') }}
            </button>
        </div>
    </form>
</div>
<script>
function orderSummary() {
    return {
        cycleName: '{{ $prices->first()->name }}',
        cyclePrice: {{ $prices->first()->type === 'free' ? 0 : ($prices->first()->rates[$currencyActive['code']]['price'] ?? 0) }},
        setupFee: {{ $prices->first()->type === 'free' ? 0 : ($prices->first()->rates[$currencyActive['code']]['setup_fee'] ?? 0) }},
        total: {{ ($prices->first()->type === 'free' ? 0 : ($prices->first()->rates[$currencyActive['code']]['price'] ?? 0)) + ($prices->first()->type === 'free' ? 0 : ($prices->first()->rates[$currencyActive['code']]['setup_fee'] ?? 0)) }},
        
        cyclePriceFormatted: '{{ $prices->first()->type === 'free' ? "Free" : Currency::format($prices->first()->rates[$currencyActive["code"]]["price"] ?? 0) }}',
        setupFeeFormatted: '{{ $prices->first()->type === "free" ? "Free" : Currency::format($prices->first()->rates[$currencyActive["code"]]["setup_fee"] ?? 0) }}',
        totalFormatted: '{{ $prices->first()->type === "free" ? "Free" : Currency::format(($prices->first()->rates[$currencyActive["code"]]["price"] ?? 0) + ($prices->first()->rates[$currencyActive["code"]]["setup_fee"] ?? 0)) }}',
        
        init() {
            console.log('Order summary initialized');
            console.log('Default cycle price:', this.cyclePrice);
            console.log('Default setup fee:', this.setupFee);
            console.log('Default total:', this.total);
        },
        
        updateOrderSummary(name, price, setupFee, formattedPrice, formattedSetupFee, formattedTotal) {
            this.cycleName = name;
            this.cyclePrice = price;
            this.setupFee = setupFee;
            this.total = price + setupFee;
            
            this.cyclePriceFormatted = formattedPrice;
            this.setupFeeFormatted = formattedSetupFee;
            this.totalFormatted = formattedTotal;
            
            console.log('Order summary updated:', {
                name: name,
                price: price,
                setupFee: setupFee,
                total: this.total,
                formattedPrice: formattedPrice,
                formattedSetupFee: formattedSetupFee,
                formattedTotal: formattedTotal
            });
        }
    };
}
</script>
@endsection