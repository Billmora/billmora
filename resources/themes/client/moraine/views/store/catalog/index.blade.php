@extends('client::layouts.app')

@section('title', "Store")

@section('body')
<div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach ($packages as $package)
        <div class="w-full h-fit bg-white border-2 border-billmora-2 rounded-xl">
            <div class="flex flex-col gap-4 p-6">
                @if ($package->icon)
                    <img src="{{ Storage::url($package->icon) }}" alt="package icon" class="max-w-48 h-auto mx-auto object-cover rounded-lg">
                @endif
                <div class="space-y-2 text-center">
                    <h4 class="text-xl text-billmora-primary font-semibold">{{ $package->name }}</h4>
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
                    <a 
                        href="{{ route('client.store.catalog.package', ['catalog' => $package->catalog->slug, 'package' => $package->slug]) }}"
                        class="flex gap-2 items-center bg-billmora-primary text-white px-3 py-2 mx-auto rounded-lg hover:text-white transition-colors duration-300"
                    >
                        {{ __('client/store.order_now') }}
                    </a>
                @else
                    <span class="flex gap-2 items-center bg-billmora-6 text-white px-3 py-2 mx-auto rounded-lg cursor-not-allowed">
                        {{ __('client/store.order_unavailable') }}
                    </span>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection