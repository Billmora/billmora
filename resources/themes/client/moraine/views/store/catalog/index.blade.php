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
                    <h4 class="text-2xl text-slate-600 font-semibold">{{ $package->name }}</h4>
                    <span>{{ $package->prices()->orderBy('id')->first()->name }}</span>
                    <p class="text-slate-500 line-clamp-3">{{ $package->description }}</p>
                </div>
                <a href="#" class="flex gap-2 items-center bg-billmora-primary text-white px-3 py-2 mx-auto rounded-lg hover:text-white transition-colors duration-300">
                    {{ __('client/store.order_now') }}
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection