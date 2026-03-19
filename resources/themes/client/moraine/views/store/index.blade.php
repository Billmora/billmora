@extends('client::layouts.app')

@section('title', 'Store')

@section('body')
<div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach ($catalogs as $catalog)
        <a
            href="{{ route('client.store.catalog', ['catalog' => $catalog->slug]) }}"
            class="flex-none bg-white border-2 border-billmora-2 rounded-2xl p-6 hover:border-billmora-primary-500 transition duration-200 grid gap-3"
        >
            @if ($catalog->icon)
                <div class="w-24 h-24 rounded-xl bg-billmora-2 flex items-center justify-center shrink-0 overflow-hidden">
                    <img
                        src="{{ Storage::url($catalog->icon) }}"
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
                {{ __('client/store.view_package') }}
                <x-lucide-arrow-right class="w-4 h-4" />
            </span>
        </a>
    @endforeach
</div>
@endsection