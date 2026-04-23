@extends('client::layouts.app')

@section('title', "Domain Details")

@section('body')
    <div class="flex flex-col gap-5">
        <div class="flex flex-col lg:flex-row gap-5">
            <div class="w-full lg:w-5/7 flex flex-col gap-5">
                <div class="w-full flex flex-col lg:flex-row gap-5">
                    <div
                        class="w-full flex justify-center bg-billmora-primary-500 p-8 border-2 border-billmora-2 rounded-2xl">
                        <div class="flex flex-col items-center m-auto text-center gap-2">
                            <span class="text-xl lg:text-3xl font-semibold text-slate-50">{{ $registrant->domain }}</span>
                        </div>
                    </div>
                    <div class="w-full grid gap-4 bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
                        <div class="grid">
                            <span class="text-sm font-semibold text-slate-500">{{ __('common.status') }}</span>
                            <span class="font-semibold text-slate-600">{{ $registrant->status }}</span>
                        </div>
                        <div class="grid">
                            <span
                                class="text-sm font-semibold text-slate-500">{{ __('client/services.price_label') }}</span>
                            <span
                                class="font-semibold text-slate-600">{{ Currency::format($registrant->price, $registrant->user->currency ?? null) }}</span>
                        </div>
                        <div class="grid">
                            <span
                                class="text-sm font-semibold text-slate-500">{{ __('client/registrants.registration_date_label') }}</span>
                            <span
                                class="font-semibold text-slate-600">{{ $registrant->registered_at?->format(Billmora::getGeneral('company_date_format')) }}</span>
                        </div>
                        <div class="grid">
                            <span
                                class="text-sm font-semibold text-slate-500">{{ __('client/registrants.expires_label') }}</span>
                            <span
                                class="font-semibold text-slate-600">{{ $registrant->expires_at?->format(Billmora::getGeneral('company_date_format')) }}</span>
                        </div>
                    </div>
                </div>

                @yield('workspaces')
            </div>

            <div class="w-full lg:w-2/7 h-fit grid gap-5">
                <div class="grid gap-4 bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
                    <a href="{{ route('client.registrants.show', ['registrant' => $registrant->registrant_number]) }}"
                        class="w-full flex gap-2 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        <x-lucide-view class="w-auto h-5" />
                        {{ __('client/services.action.overview') }}
                    </a>

                    @if($registrant->status === 'active' && $registrant->plugin_id)
                        <a href="{{ route('client.registrants.nameservers.show', ['registrant' => $registrant->registrant_number]) }}"
                            class="w-full flex gap-2 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            <x-lucide-network class="w-auto h-5" />
                            {{ __('client/registrants.nameservers_label') }}
                        </a>
                    @endif

                    <a href="{{ route('client.registrants.autorenew.show', ['registrant' => $registrant->registrant_number]) }}"
                        class="w-full flex gap-2 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        <x-lucide-refresh-cw class="w-auto h-5" />
                        {{ __('client/registrants.auto_renew_label') }}
                    </a>
                </div>

                @if(!empty($clientActions))
                    <div class="grid gap-4 bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
                        @foreach($clientActions as $slug => $action)
                            @if(in_array($action['type'], ['page', 'form']))
                                <a href="{{ route('client.registrants.registrar.show', ['registrant' => $registrant->registrant_number, 'slug' => $slug]) }}"
                                    class="w-full flex gap-3 items-center bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-all duration-200 cursor-pointer">
                                    @if(!empty($action['icon']))
                                        <i class="{{ $action['icon'] }} w-auto h-5"></i>
                                    @endif
                                    <span class="font-medium">{{ $action['label'] }}</span>
                                </a>
                            @elseif($action['type'] === 'link')
                                <a href="{{ route('client.registrants.registrar.handle', ['registrant' => $registrant->registrant_number, 'slug' => $slug]) }}"
                                    target="_blank"
                                    class="w-full flex gap-3 items-center bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-all duration-200 cursor-pointer">
                                    @if(!empty($action['icon']))
                                        <i class="{{ $action['icon'] }} w-auto h-5"></i>
                                    @endif
                                    <span class="font-medium">{{ $action['label'] }}</span>
                                </a>
                            @elseif($action['type'] === 'submit')
                                <x-client::modal.trigger modal="modalAction-{{ $slug }}"
                                    class="w-full flex gap-3 items-center bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-all duration-200 cursor-pointer">
                                    @if(!empty($action['icon']))
                                        <i class="{{ $action['icon'] }} w-auto h-5"></i>
                                    @endif
                                    <span class="font-medium">{{ $action['label'] }}</span>
                                </x-client::modal.trigger>
                                <x-client::modal.content modal="modalAction-{{ $slug }}" variant="danger" size="xl" position="centered"
                                    title="{{ __('common.confirm_modal_title')}}"
                                    description="{{ __('common.confirm_modal_description', ['item' => $action['label']]) }}">
                                    <form
                                        action="{{ route('client.registrants.registrar.handle', ['registrant' => $registrant->registrant_number, 'slug' => $slug]) }}"
                                        method="POST">
                                        @csrf
                                        @if(in_array(strtoupper($action['method'] ?? 'POST'), ['PUT', 'PATCH', 'DELETE']))
                                            @method(strtoupper($action['method']))
                                        @endif
                                        <div class="flex justify-end gap-2 mt-4">
                                            <x-client::modal.trigger type="button" variant="close"
                                                class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                                                {{ __('common.cancel') }}
                                            </x-client::modal.trigger>
                                            <button type="submit"
                                                class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
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
    </div>
@endsection