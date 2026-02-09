@extends('client::layouts.app')

@section('title', "Service")

@section('body')
<div class="flex flex-col lg:flex-row gap-5">
    <div class="w-full lg:w-5/7 flex flex-col gap-5">
        <div class="w-full flex flex-col lg:flex-row gap-5">
            <div class="w-full flex justify-center bg-billmora-primary p-8 border-2 border-billmora-2 rounded-2xl">
                <div class="flex flex-col items-center m-auto">
                    <span class="text-xl lg:text-3xl font-semibold text-slate-50">{{ $service->package->catalog->name }}</span>
                    <span class="text-md lg:text-2xl font-medium text-slate-100">{{ $service->name }}</span>
                </div>
            </div>
            <div class="w-full grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <div class="grid">
                    <span class="text-sm font-semibold text-slate-500">Status</span>
                    <span class="font-semibold text-slate-600">{{ $service->status }}</span>
                </div>
                <div class="grid">
                    <span class="text-sm font-semibold text-slate-500">Registration Date</span>
                    <span class="font-semibold text-slate-600">{{ $service->created_at->format(Billmora::getGeneral('company_date_format')) }}</span>
                </div>
                <div class="grid">
                    <span class="text-sm font-semibold text-slate-500">Price</span>
                    <span class="font-semibold text-slate-600">{{ Currency::format($service->price, $service->currency) }}</span>
                </div>
                <div class="grid">
                    <span class="text-sm font-semibold text-slate-500">Setup Fee</span>
                    <span class="font-semibold text-slate-600">{{ Currency::format($service->setup_fee, $service->currency) }}</span>
                </div>
                <div class="grid">
                    <span class="text-sm font-semibold text-slate-500">Billing Cycle</span>
                    <span class="font-semibold text-slate-600">{{ $service->cycle_label }}</span>
                </div>
                @if ($service->billing_type === 'recurring')
                    <div class="grid">
                        <span class="text-sm font-semibold text-slate-500">Expires At</span>
                        <span class="font-semibold text-slate-600">{{ $service->next_due_date?->format(Billmora::getGeneral('company_date_format')) }}</span>
                    </div>
                @endif
            </div>
        </div>
        @if($variantOptions->isNotEmpty())
            <div class="bg-white border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
                    <h3 class="font-semibold text-slate-600">Variant Options</h3>
                </div>
                <ul class="grid gap-4 p-6">
                    @foreach($variantOptions as $option)
                        @if(!$loop->first)
                            <hr class="border-t-2 border-billmora-2">
                        @endif
                        <li class="grid grid-cols-2 text-start">
                            <span class="text-slate-500 font-semibold">
                                {{ $option->variant->name }}
                            </span>
                            <span class="text-slate-600 font-semibold">
                                {{ $option->name }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <div class="w-full lg:w-2/7 h-fit grid gap-5">
        <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <a href="{{ route('client.services.show', ['service' => $service->id]) }}" class="w-full flex gap-2 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-view class="w-auto h-5" />
                Overview
            </a>
            {{-- TODO: Add package upgrade/downgrade --}}
            <a href="#" class="w-full flex gap-2 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-arrow-down-up class="w-auto h-5" />
                Adjustment Package
            </a>
            {{-- TODO: Add variant upgrade/downgrade --}}
            <a href="#" class="w-full flex gap-2 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-arrow-down-up class="w-auto h-5" />
                Adjustment Variant
            </a>
            @if ($service->package->allow_cancellation)
                <x-client::modal.trigger modal="cancelServiceModal" class="w-full flex gap-2 items-center bg-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    <x-lucide-ban class="w-auto h-5" />
                    Request Cancellation
                </x-client::modal.trigger>
            @endif
        </div>
    </div>
</div>
@if ($service->package->allow_cancellation)
    <x-admin::modal.content
        modal="cancelServiceModal"
        variant="danger"
        size="xl"
        position="centered"
        title="{{ __('common.cancel_modal_title') }}"
        description="{{ __('common.cancel_modal_description', ['item' => $service->name]) }}">
        <form action="#" method="POST">
            @csrf
            <div class="flex justify-end gap-2 mt-4">
                <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.submit') }}</button>
            </div>
        </form>
    </x-admin::modal.content>
@endif
@endsection