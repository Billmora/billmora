@extends('client::layouts.app')

@section('title', 'Store')

@section('body')
    @if(Billmora::getGeneral('domain_registration_enabled') || Billmora::getGeneral('domain_transfer_enabled'))
        <div class="mb-8">
            <a href="{{ route('client.store.domains') }}"
                class="group block relative overflow-hidden rounded-2xl bg-linear-to-br from-billmora-primary-500 to-billmora-primary-700 p-8 sm:p-10 transition-transform duration-300">
                <!-- Decorative Background Elements -->
                <div class="absolute -right-10 -top-10 w-64 h-64 bg-white/10 rounded-full blur-3xl">
                </div>
                <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl">
                </div>

                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-center gap-6">
                        <div
                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/20">
                            <x-lucide-globe class="w-8 h-8 sm:w-10 sm:h-10 text-white" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <h2 class="text-white font-extrabold text-2xl sm:text-3xl tracking-tight">
                                {{ __('client/store.domain_search_label') }}
                            </h2>
                            <p class="text-white/80 text-sm sm:text-base max-w-xl">
                                {{ __('client/store.domain_search_placeholder') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 md:mt-0 shrink-0">
                        <span
                            class="inline-flex items-center gap-2 bg-white text-billmora-primary-600 hover:bg-billmora-primary-50 hover:text-billmora-primary-700 font-bold px-6 py-3 rounded-xl transition duration-300">
                            {{ __('common.search') ?? 'Search Now' }}
                            <x-lucide-arrow-right class="w-5 h-5" />
                        </span>
                    </div>
                </div>
            </a>
        </div>
    @endif

    <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($catalogs as $catalog)
            <a href="{{ route('client.store.catalog', ['catalog' => $catalog->slug]) }}"
                class="flex-none bg-white border-2 border-billmora-2 rounded-2xl p-6 hover:border-billmora-primary-500 transition duration-200 grid gap-3">
                @if ($catalog->icon)
                    <div class="w-24 h-24 rounded-xl bg-billmora-2 flex items-center justify-center shrink-0 overflow-hidden">
                        <img src="{{ Storage::url($catalog->icon) }}" alt="{{ $catalog->name }}"
                            class="w-full h-full object-contain p-3">
                    </div>
                @endif
                <div class="grid gap-1 min-w-0 mb-auto">
                    <span class="text-slate-700 font-semibold text-lg truncate">{{ $catalog->name }}</span>
                    <p class="text-slate-500 text-sm line-clamp-2">{!! $catalog->description !!}</p>
                </div>
                <span class="text-billmora-primary-500 text-sm font-semibold inline-flex items-center gap-1 mt-auto">
                    {{ __('client/store.view_package') }}
                    <x-lucide-arrow-right class="w-4 h-4" />
                </span>
            </a>
        @endforeach
    </div>
@endsection