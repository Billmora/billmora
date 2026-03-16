@extends('portal::layouts.app')

@section('body')
<div class="absolute inset-0 h-full w-full bg-billmora-1 bg-[radial-gradient(#e5e7eb_1.5px,transparent_1.5px)] bg-size-[20px_20px] -z-10"></div>
<section class="max-w-7xl mx-auto">
    <div class="w-full min-h-dvh grid md:grid-cols-2 justify-around items-center px-4">
        <div class="grid gap-4">
            <h1 class="text-5xl text-billmora-primary-500 font-semibold">{{ $portalThemeConfig['hero_title'] }}</h1>
            <p class="text-xl text-slate-500">{{ $portalThemeConfig['hero_description'] }}</p>
            <div class="flex gap-2">
                <a href="{{ route('client.store') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 text-white rounded-lg transition">
                    {{ __('portal.get_started') }}
                </a>
            </div>
        </div>
        <div class="hidden md:flex ml-auto">
            <img src="https://media.billmora.com/logo/main-bgnone.png" alt="hero image" class="w-80 h-auto">
        </div>
    </div>
</section>
<section class="bg-billmora-bg py-16">
    <div class="max-w-7xl mx-auto px-4 grid gap-10">
        <div class="grid text-center gap-1">
            <h3 class="text-2xl text-slate-700 font-semibold">{{ $portalThemeConfig['catalog_title'] }}</h3>
            <span class="text-lg text-slate-500">{{ $portalThemeConfig['catalog_description'] }}</span>
        </div>
		<div
			x-data="{
				current: 0,
				total: {{ $catalogs->count() }},
				cols: 1,
				init() {
					this.updateCols();
					window.addEventListener('resize', () => {
						this.updateCols();
						this.current = Math.min(this.current, this.maxIndex());
					});
				},
				updateCols() {
					if (window.innerWidth >= 1024) this.cols = 3;
					else if (window.innerWidth >= 768) this.cols = 2;
					else this.cols = 1;
				},
				maxIndex() {
					return Math.max(0, this.total - this.cols);
				},
				prev() { if (this.current > 0) this.current-- },
				next() { if (this.current < this.maxIndex()) this.current++ },
			}"
			class="relative min-w-0"
		>
			<div class="overflow-hidden w-full">
				<div
					class="flex w-full transition-transform duration-300 ease-in-out gap-4"
					:style="`transform: translateX(calc(-${current} * (100% / ${cols} + 16px / ${cols})))`"
				>
					@foreach ($catalogs as $catalog)
						<a
							href="{{ route('client.store.catalog', ['catalog' => $catalog->slug]) }}"
							class="flex-none bg-white border-2 border-billmora-2 rounded-2xl p-6 hover:border-billmora-primary-500 transition duration-200 grid gap-3"
							:style="`width: calc(${100 / cols}% - ${16 * (cols - 1) / cols}px)`"
						>
							@if ($catalog->icon)
								<div class="w-24 h-24 rounded-xl bg-billmora-2 flex items-center justify-center shrink-0 overflow-hidden">
									<img
										src="{{ $catalog->icon }}"
										alt="{{ $catalog->name }}"
										class="w-full h-full object-contain p-3"
									>
								</div>
							@endif
							<div class="grid gap-1 min-w-0 mb-auto">
								<span class="text-slate-700 font-semibold text-lg truncate">{{ $catalog->name }}</span>
								<p class="text-slate-500 text-sm line-clamp-2">{!! $catalog->description !!}</p>
							</div>
							<span class="text-billmora-primary-500 text-sm font-semibold inline-flex items-center gap-1 mt-auto">
								{{ __('portal.explore') }}
								<x-lucide-arrow-right class="w-4 h-4" />
							</span>
						</a>
					@endforeach
				</div>
			</div>
			@if ($catalogs->count() > 1)
				<div class="flex justify-center items-center gap-3 mt-6">
					<button
						type="button"
						x-on:click="prev"
						:disabled="current === 0"
						class="p-2 rounded-full border-2 border-billmora-2 text-slate-500 hover:border-billmora-primary-500 hover:text-billmora-primary-500 disabled:opacity-30 disabled:cursor-not-allowed transition"
					>
						<x-lucide-chevron-left class="w-5 h-5" />
					</button>
					<div class="flex gap-2">
						<template x-for="i in (maxIndex() + 1)" :key="i">
							<button
								type="button"
								x-on:click="current = i - 1"
								class="h-2 rounded-full transition-all duration-200"
								:class="current === i - 1 ? 'bg-billmora-primary-500 w-4' : 'bg-billmora-2 w-2'"
							></button>
						</template>
					</div>
					<button
						type="button"
						x-on:click="next"
						:disabled="current >= maxIndex()"
						class="p-2 rounded-full border-2 border-billmora-2 text-slate-500 hover:border-billmora-primary-500 hover:text-billmora-primary-500 disabled:opacity-30 disabled:cursor-not-allowed transition"
					>
						<x-lucide-chevron-right class="w-5 h-5" />
					</button>
				</div>
			@endif
		</div>
    </div>
</section>
<section class="py-16 px-4">
    <div class="max-w-3xl mx-auto bg-billmora-primary-500 rounded-3xl px-8 py-12 text-center grid gap-6 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-10 -left-10 w-56 h-56 bg-white/10 rounded-full"></div>
        <div class="relative grid gap-2">
            <h3 class="text-3xl text-white font-bold">{{ $portalThemeConfig['cta_title'] }}</h3>
            <p class="text-lg text-white/80">{{ $portalThemeConfig['cta_description'] }}</p>
        </div>
        <div class="relative flex justify-center gap-3 flex-wrap">
            @guest
                <a href="{{ route('client.register') }}" class="bg-billmora-bg px-6 py-3 text-billmora-primary-500 font-semibold rounded-lg transition">
                    {{ __('common.sign_up') }}
                </a>
                <a href="{{ route('client.login') }}" class="bg-billmora-bg/20 px-6 py-3 text-white font-semibold rounded-lg transition">
                    {{ __('common.sign_in') }}
                </a>
            @else
                <a href="{{ route('client.dashboard') }}" class="bg-billmora-bg px-6 py-3 text-billmora-primary-500 font-semibold rounded-lg transition">
                    {{ __('portal.client_area') }}
                </a>
            @endguest
        </div>
    </div>
</section>
@endsection