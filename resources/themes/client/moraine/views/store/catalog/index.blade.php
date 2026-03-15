@extends('client::layouts.app')

@section('title', "Store")

@section('body')
<div class="mb-6">
    <h2 class="text-3xl text-billmora-primary-500 font-semibold">{{ $catalog->name }}</h2>
    <p class="text-slate-500">{{ $catalog->description }}</p>
</div>
<div class="grid grid-cols-1 gap-5">
    <div 
        class="bg-billmora-bg rounded-2xl max-w-80"
        x-data="{ base: '{{ route('client.store.catalog', $catalog) }}'.replace('{{ $catalog->slug }}', '') }"
        x-on:change="window.location.href = base + $event.target.value"
    >
        <x-client::select
            name="catalog"
        >
            @foreach ($catalogs as $item)
                <option value="{{ $item->slug }}" {{ $catalog->slug === $item->slug ? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </x-client::select>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($packages as $package)
            <div class="w-full h-fit bg-billmora-bg border-2 border-billmora-2 rounded-xl">
                <div class="flex flex-col gap-4 p-6">
                    @if ($package->icon)
                        <img src="{{ Storage::url($package->icon) }}" alt="package icon" class="max-w-48 h-auto mx-auto object-cover rounded-lg">
                    @endif
                    <div class="space-y-2 text-center">
                        <h4 class="text-xl text-billmora-primary-500 font-semibold">{{ $package->name }}</h4>
                        @if ($package->prices->contains(fn ($p) => $p->type === 'free'))
                            <div class="grid">
                                <span class="text-xl text-slate-500 font-semibold">Free</span>
                                <span class="text-sm text-slate-400 font-semibold">
                                    {{ $package->prices->firstWhere('type', 'free')->name }}
                                </span>
                            </div>
                        @elseif (
                            $package->prices->contains(fn ($p) =>
                                $p->type !== 'free'
                                && isset($p->rates[$currencyActive['code']])
                                && ($p->rates[$currencyActive['code']]['enabled'] ?? false)
                                && ($p->rates[$currencyActive['code']]['price'] ?? null) !== null
                            )
                        )
                            <div class="grid">
                                <span class="text-xl text-slate-500 font-semibold">
                                    {{ Currency::format(
                                        $package->primaryPrice->rates[$currencyActive['code']]['price'],
                                        $currencyActive['code']
                                    ) }}
                                </span>
                                <span class="text-sm text-slate-400 font-semibold">
                                    {{ $package->primaryPrice->name }}
                                </span>
                            </div>
                        @else
                            <div class="grid">
                                <span class="text-xl text-slate-500 font-semibold">
                                    {{ __('client/store.unavailable_currency') }}
                                </span>
                            </div>
                        @endif
                        <p class="text-slate-500">{!! $package->description !!}</p>
                    </div>
                    @if (
                        $package->prices->contains(fn ($p) => $p->type === 'free')
                        || $package->prices->contains(fn ($p) =>
                            $p->type !== 'free'
                            && isset($p->rates[$currencyActive['code']])
                            && ($p->rates[$currencyActive['code']]['enabled'] ?? false)
                            && ($p->rates[$currencyActive['code']]['price'] ?? null) !== null
                        )
                    )
                        @if ($package->stock !== 0) 
                            <a 
                                href="{{ route('client.store.catalog.package', ['catalog' => $package->catalog->slug, 'package' => $package->slug]) }}"
                                class="flex gap-2 items-center bg-billmora-primary-500 text-white px-3 py-2 mx-auto rounded-lg hover:text-white transition-colors duration-300"
                            >
                                {{ __('client/store.order_now') }}
                            </a>
                            @if ($package->stock >= 1)
                                <span class="text-center text-sm text-slate-500 font-semibold">{{ __('client/store.stock_available', ['item' => $package->stock]) }}</span>
                            @endif
                        @else
                            <span class="flex gap-2 items-center bg-billmora-6 text-white px-3 py-2 mx-auto rounded-lg cursor-not-allowed">
                                {{ __('client/store.order_out_of_stock') }}
                            </span>
                        @endif
                    @else
                        <span class="flex gap-2 items-center bg-billmora-6 text-white px-3 py-2 mx-auto rounded-lg cursor-not-allowed">
                            {{ __('client/store.order_unavailable') }}
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection