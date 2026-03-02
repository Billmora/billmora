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
                    <span class="text-sm font-semibold text-slate-500">{{ __('common.status') }}</span>
                    <span class="font-semibold text-slate-600">{{ $service->status }}</span>
                </div>
                <div class="grid">
                    <span class="text-sm font-semibold text-slate-500">{{ __('client/services.registration_label') }}</span>
                    <span class="font-semibold text-slate-600">{{ $service->created_at->format(Billmora::getGeneral('company_date_format')) }}</span>
                </div>
                <div class="grid">
                    <span class="text-sm font-semibold text-slate-500">{{ __('client/services.price_label') }}</span>
                    <span class="font-semibold text-slate-600">{{ Currency::format($service->price, $service->currency) }}</span>
                </div>
                <div class="grid">
                    <span class="text-sm font-semibold text-slate-500">{{ __('client/services.setup_fee_label') }}</span>
                    <span class="font-semibold text-slate-600">{{ Currency::format($service->setup_fee, $service->currency) }}</span>
                </div>
                <div class="grid">
                    <span class="text-sm font-semibold text-slate-500">{{ __('client/services.billing_cycle_label') }}</span>
                    <span class="font-semibold text-slate-600">{{ $service->cycle_label }}</span>
                </div>
                @if ($service->billing_type === 'recurring')
                    <div class="grid">
                        <span class="text-sm font-semibold text-slate-500">{{ __('client/services.expires_label') }}</span>
                        <span class="font-semibold text-slate-600">{{ $service->next_due_date?->format(Billmora::getGeneral('company_date_format')) }}</span>
                    </div>
                @endif
            </div>
        </div>
        @yield('workspaces')
    </div>
    <div class="w-full lg:w-2/7 h-fit grid gap-5">
        <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <a href="{{ route('client.services.show', ['service' => $service->id]) }}" class="w-full flex gap-2 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-view class="w-auto h-5" />
                {{ __('client/services.action.overview') }}
            </a>
            {{-- TODO: Add package/variant scaling --}}
            <a href="#" class="w-full flex gap-2 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-arrow-down-up class="w-auto h-5" />
                Adjustment Package
            </a>
            @if ($service->package->allow_cancellation)
                <x-client::modal.trigger modal="cancelServiceModal" class="w-full flex gap-2 items-center bg-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    <x-lucide-ban class="w-auto h-5" />
                    {{ __('client/services.action.cancel') }}
                </x-client::modal.trigger>
            @endif
        </div>
        @if(!empty($clientActions))
            <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                @foreach($clientActions as $slug => $action)
                    @if(in_array($action['type'], ['page', 'form']))
                        <a href="{{ route('client.services.provisioning.show', ['service' => $service->id, 'slug' => $slug]) }}"
                           class="w-full flex gap-3 items-center bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-all duration-200 cursor-pointer"
                        >
                            @if(!empty($action['icon']))
                                <i class="{{ $action['icon'] }}"></i>
                            @endif
                            <span class="font-medium">{{ $action['label'] }}</span>
                        </a>
                    @elseif($action['type'] === 'link')
                        <a href="{{ route('client.services.provisioning.handle', ['service' => $service->id, 'slug' => $slug]) }}"
                           target="_blank"
                           class="w-full flex gap-3 items-center bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-all duration-200 cursor-pointer"
                        >
                            @if(!empty($action['icon']))
                                <i class="{{ $action['icon'] }}"></i>
                            @endif
                            <span class="font-medium">{{ $action['label'] }}</span>
                        </a>
                    @elseif($action['type'] === 'submit')
                        <x-client::modal.trigger 
                            modal="modalAction-{{ $slug }}"
                            class="w-full flex gap-3 items-center bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-all duration-200 cursor-pointer"
                        >
                            @if(!empty($action['icon']))
                                <i class="{{ $action['icon'] }}"></i>
                            @endif
                            <span class="font-medium">{{ $action['label'] }}</span>
                        </x-client::modal.trigger>
                        <x-client::modal.content
                            modal="modalAction-{{ $slug }}"
                            variant="danger"
                            size="xl"
                            position="centered"
                            title="{{ __('common.confirm_modal_title')}}"
                            description="{{ __('common.confirm_modal_description', ['item' => $action['label']]) }}"
                        >
                            <form action="{{ route('client.services.provisioning.handle', ['service' => $service->id, 'slug' => $slug]) }}" method="POST">
                                @csrf
                                @if(in_array(strtoupper($action['method'] ?? 'POST'), ['PUT', 'PATCH', 'DELETE']))
                                    @method(strtoupper($action['method']))
                                @endif
                                <div class="flex justify-end gap-2 mt-4">
                                    <x-client::modal.trigger 
                                        type="button" 
                                        variant="close" 
                                        class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                                    >
                                        {{ __('common.cancel') }}
                                    </x-client::modal.trigger>
                                    <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                                        {{ __('common.submit') }}
                                    </button>
                                </div>
                            </form>
                        </x-client::modal.content>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
@if ($service->package->allow_cancellation)
    <x-client::modal.content
        modal="cancelServiceModal"
        variant="danger"
        size="xl"
        position="centered"
        title="{{ __('common.cancel_modal_title') }}"
        description="{{ __('common.cancel_modal_description', ['item' => $service->name]) }}">
        <form action="#" method="POST">
            @csrf
            <div class="flex justify-end gap-2 mt-4">
                <x-client::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-client::modal.trigger>
                <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.submit') }}</button>
            </div>
        </form>
    </x-client::modal.content>
@endif
@endsection