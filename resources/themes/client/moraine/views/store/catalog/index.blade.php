@extends('client::layouts.app')

@section('title', "Store")

@section('body')
<div class="mb-8">
    <h2 class="text-3xl text-billmora-primary-500 font-semibold">{{ $catalog->name }}</h2>
    <p class="text-slate-500 mt-1">{{ $catalog->description }}</p>
</div>
<div class="grid grid-cols-1 gap-6">
    <div 
        class="bg-billmora-bg rounded-2xl max-w-80"
        x-data="{ base: '{{ route('client.store.catalog', $catalog) }}'.replace('{{ $catalog->slug }}', '') }"
        x-on:change="window.location.href = base + $event.target.value"
    >
        <x-client::select name="catalog">
            @foreach ($catalogs as $item)
                <option value="{{ $item->slug }}" {{ $catalog->slug === $item->slug ? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </x-client::select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($packages as $package)
            @php
                $isFree = $package->prices->contains(fn ($p) => $p->type === 'free');
                $availablePrice = $package->prices->first(fn ($p) =>
                    $p->type !== 'free'
                    && isset($p->rates[$currencyActive['code']])
                    && ($p->rates[$currencyActive['code']]['enabled'] ?? false)
                    && ($p->rates[$currencyActive['code']]['price'] ?? null) !== null
                );
                $hasPrice = $availablePrice !== null;
                $isAvailable = $isFree || $hasPrice;
                $isOrderable = $isAvailable && $package->stock !== 0;
            @endphp
            <div class="relative flex flex-col h-full bg-billmora-bg border-2 border-billmora-2 rounded-2xl overflow-hidden hover:border-billmora-primary-500 transition-all duration-200">
                @if ($isAvailable && $package->stock === 0)
                    <span class="absolute top-0 right-0 bg-slate-400 text-white text-xs uppercase tracking-wider font-bold px-3 py-1 rounded-bl-xl rounded-tr-2xl z-10">
                        {{ __('client/store.order_out_of_stock') }}
                    </span>
                @elseif (!$isAvailable)
                    <span class="absolute top-0 right-0 bg-slate-400 text-white text-xs uppercase tracking-wider font-bold px-3 py-1 rounded-bl-xl rounded-tr-2xl z-10">
                        {{ __('client/store.order_unavailable') }}
                    </span>
                @endif
                <div class="flex flex-col gap-4 p-6 flex-1">
                    @if ($package->icon)
                        <img 
                            src="{{ Storage::url($package->icon) }}" 
                            alt="{{ $package->name }}" 
                            class="max-w-40 h-auto mx-auto object-contain rounded-lg"
                        >
                    @endif
                    <div class="space-y-2 text-center flex-1">
                        <h4 class="text-xl text-billmora-primary-500 font-semibold">{{ $package->name }}</h4>

                        @if ($isFree)
                            <div class="grid">
                                <span class="text-2xl text-slate-700 font-bold">{{ __('billing.cycles.free') }}</span>
                                <span class="text-sm text-slate-400 font-semibold">
                                    {{ $package->prices->firstWhere('type', 'free')->name }}
                                </span>
                            </div>
                        @elseif ($hasPrice)
                            <div class="grid">
                                <span class="text-2xl text-slate-700 font-bold">
                                    {{ Currency::format(
                                        $availablePrice->rates[$currencyActive['code']]['price'],
                                        $currencyActive['code']
                                    ) }}
                                </span>
                                <span class="text-sm text-slate-400 font-semibold">
                                    {{ $availablePrice->name }}
                                </span>
                            </div>
                        @else
                            <span class="text-lg text-slate-400 font-semibold">
                                {{ __('client/store.unavailable_currency') }}
                            </span>
                        @endif

                        <p class="text-slate-500 text-sm leading-relaxed">{!! $package->description !!}</p>
                    </div>
                    <div class="mt-auto pt-4 border-t-2 border-billmora-2 grid gap-2">
                        @if ($isOrderable)
                            <a 
                                href="{{ route('client.store.catalog.package', ['catalog' => $package->catalog->slug, 'package' => $package->slug]) }}"
                                class="w-full text-center bg-billmora-primary-500 hover:bg-billmora-primary-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors duration-200"
                            >
                                {{ __('client/store.order_now') }}
                            </a>
                            @if ($package->stock >= 1)
                                <span class="block text-center text-xs text-emerald-600 font-semibold bg-emerald-50 py-1 rounded-lg">
                                    {{ __('client/store.stock_available', ['item' => $package->stock]) }}
                                </span>
                            @endif
                        @else
                            <span class="w-full text-center bg-billmora-2 text-slate-400 text-sm font-semibold px-4 py-2.5 rounded-lg cursor-not-allowed">
                                {{ $package->stock === 0 ? __('client/store.order_out_of_stock') : __('client/store.order_unavailable') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
