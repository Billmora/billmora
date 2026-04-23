@php
    $registrationEnabled = Billmora::getGeneral('domain_registration_enabled');
    $transferEnabled = Billmora::getGeneral('domain_transfer_enabled');
    $anyEnabled = $registrationEnabled || $transferEnabled;
@endphp
<div class="flex flex-col gap-5 max-w-4xl mx-auto mt-10">
    <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
        <h1 class="text-2xl font-bold text-slate-700 text-center mb-6">{{ __('client/store.domain_search_label') }}</h1>

        @if(!$anyEnabled)
            <div class="text-center py-8">
                <x-lucide-globe class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                <p class="text-slate-500 font-semibold">{{ __('client/store.domain_disabled') }}</p>
            </div>
        @else

        @if($registrationEnabled && $transferEnabled)
            <div class="flex justify-center mb-6">
                <div class="flex bg-billmora-2 rounded-lg p-1">
                    <button wire:click="setType('register')" class="px-6 py-2 rounded-md font-semibold transition-colors outline-none cursor-pointer {{ $type === 'register' ? 'bg-white text-billmora-primary-500 shadow' : 'text-slate-500 hover:text-slate-700' }}">
                        {{ __('client/store.domain_register_tab') }}
                    </button>
                    <button wire:click="setType('transfer')" class="px-6 py-2 rounded-md font-semibold transition-colors outline-none cursor-pointer {{ $type === 'transfer' ? 'bg-white text-billmora-primary-500 shadow' : 'text-slate-500 hover:text-slate-700' }}">
                        {{ __('client/store.domain_transfer_tab') }}
                    </button>
                </div>
            </div>
        @elseif($registrationEnabled)
            <div class="flex justify-center mb-6">
                <span class="px-4 py-2 bg-billmora-2 rounded-lg text-billmora-primary-500 font-semibold text-sm">
                    {{ __('client/store.domain_register_tab') }}
                </span>
            </div>
        @elseif($transferEnabled)
            <div class="flex justify-center mb-6">
                <span class="px-4 py-2 bg-billmora-2 rounded-lg text-billmora-primary-500 font-semibold text-sm">
                    {{ __('client/store.domain_transfer_tab') }}
                </span>
            </div>
        @endif

        <form wire:submit.prevent="search" class="flex flex-col md:flex-row gap-3 relative">
            <input type="text" wire:model.defer="domain" placeholder="{{ __('client/store.domain_search_placeholder') }}" class="w-full px-6 py-4 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl focus:border-billmora-primary-500 outline-none transition-colors text-lg font-medium">
            <button type="submit" class="w-full md:w-auto px-8 py-4 bg-billmora-primary-500 hover:bg-billmora-primary-600 text-white rounded-xl font-bold text-lg transition-colors shadow cursor-pointer">
                <span wire:loading.remove wire:target="search">{{ __('common.search') }}</span>
                <span wire:loading wire:target="search">...</span>
            </button>
        </form>
        @error('domain')
            <p class="text-red-500 mt-2 text-sm font-semibold">{{ $message }}</p>
        @enderror

        @endif 
    </div>

    @if($searched && $tld)
        <div class="bg-billmora-bg p-6 md:p-8 border-2 {{ $available ? 'border-billmora-primary-500' : 'border-red-500' }} rounded-2xl mb-5">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
                <div class="w-full md:w-auto overflow-hidden">
                    <h2 class="text-xl font-bold text-slate-700 mb-2 md:mb-1 truncate">{{ $domainName }}</h2>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                        @if($available)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-bold">
                                <x-lucide-check-circle class="w-4 h-4" />
                                {{ __('client/store.domain_available') }}
                            </span>
                            @if($checkPrice !== null)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-sm font-bold">
                                    Premium: {{ Currency::format($checkPrice) }}
                                </span>
                            @elseif($tldPrice)
                                @php
                                    $basePrice = $type === 'register' ? $tldPrice->register_price : $tldPrice->transfer_price;
                                    $minYears = $tld->min_years;
                                    $totalBasePrice = $basePrice * $minYears;
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-bold">
                                    {{ Currency::format($totalBasePrice) }} / {{ $minYears }} {{ $minYears > 1 ? 'Years' : 'Year' }}
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm font-bold">
                                <x-lucide-x-circle class="w-4 h-4" />
                                {{ __('client/store.domain_unavailable') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="w-full md:w-auto mt-2 md:mt-0">
                    @if($available)
                        <a href="{{ route('client.store.domains.show', ['domain_name' => $domainName, 'type' => $type]) }}" class="w-full md:w-auto px-6 py-3 md:py-2.5 bg-billmora-primary-500 hover:bg-billmora-primary-600 text-white rounded-lg font-bold transition-colors whitespace-nowrap shadow flex items-center justify-center gap-2 cursor-pointer">
                            {{ __('common.configure') }}
                            <x-lucide-arrow-right class="w-5 h-5" />
                        </a>
                    @else
                        <button disabled class="w-full md:w-auto px-6 py-3 md:py-2.5 bg-slate-300 text-slate-500 rounded-lg font-bold whitespace-nowrap flex items-center justify-center gap-2 cursor-not-allowed">
                            Taken
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if($loadingSuggestions)
        <div wire:init="loadSuggestions" class="bg-white p-6 border-2 border-billmora-2 rounded-2xl animate-pulse">
            <div class="h-6 bg-slate-200 rounded w-1/3 md:w-1/4 mb-5 mx-auto md:mx-0"></div>
            <div class="flex flex-col gap-3">
                @for($i = 0; $i < 3; $i++)
                <div class="h-[72px] md:h-16 bg-slate-50 rounded-xl border border-slate-200 w-full"></div>
                @endfor
            </div>
        </div>
        @elseif(count($alternativeNames) > 0 || count($suggestions) > 0)
        <div class="bg-white p-6 border-2 border-billmora-2 rounded-2xl">
            <h3 class="text-lg font-bold text-slate-700 mb-4">Recommendations</h3>
            <div class="flex flex-col gap-3">
                @foreach(array_merge($alternativeNames, $suggestions) as $suggest)
                <div class="flex flex-col md:flex-row justify-between items-center gap-2 bg-slate-50 p-4 rounded-xl border border-slate-200 hover:border-billmora-primary-500 transition-colors">
                    <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 text-center md:text-left">
                        <span class="font-bold text-slate-700 text-lg truncate">{{ $suggest['domain'] }}</span>
                        <div class="flex justify-center md:justify-start">
                            @if($suggest['premium'] ?? false)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-sm font-bold w-fit">
                                    Premium: {{ Currency::format($suggest['price']) }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-bold w-fit">
                                    {{ Currency::format($suggest['price']) }} / {{ $suggest['min_years'] }} {{ $suggest['min_years'] > 1 ? 'Years' : 'Year' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('client.store.domains.show', ['domain_name' => $suggest['domain'], 'type' => $type]) }}" class="mt-3 md:mt-0 px-5 py-2.5 bg-billmora-primary-500 hover:bg-billmora-primary-600 text-white rounded-lg font-bold transition-colors cursor-pointer whitespace-nowrap w-full md:w-auto text-center shadow flex items-center justify-center gap-1">
                        {{ __('client/store.order_now') }}
                        <x-lucide-arrow-right class="w-4 h-4" />
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif
</div>
