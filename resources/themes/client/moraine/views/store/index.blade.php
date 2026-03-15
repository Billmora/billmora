@extends('client::layouts.app')

@section('title', 'Store')

@section('body')
<div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach ($catalogs as $catalog)
        <div class="w-full h-fit bg-billmora-bg border-2 border-billmora-2 rounded-xl">
            <div class="flex flex-col gap-4 p-6">
                @if ($catalog->icon)
                    <img src="{{ Storage::url($catalog->icon) }}" alt="catalog icon" class="w-auto max-h-48 object-cover rounded-lg">
                @endif
                <div class="space-y-2">
                    <h4 class="text-lg text-slate-600 font-semibold">{{ $catalog->name }}</h4>
                    <p class="text-slate-500 line-clamp-3">{{ $catalog->description }}</p>
                </div>
                <a href="{{ route('client.store.catalog', ['catalog' => $catalog->slug]) }}" class="flex items-center text-billmora-primary-500 font-semibold text-end ml-auto hover:text-billmora-primary-600 transition">
                    {{ __('client/store.view_package') }}
                    <x-lucide-chevron-right class="w-5 h-auto" />
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection