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
            <button type="submit" class="px-8 py-4 bg-billmora-primary-500 hover:bg-billmora-primary-600 text-white rounded-xl font-bold text-lg transition-colors shadow cursor-pointer">
                <span wire:loading.remove wire:target="search">{{ __('common.search') }}</span>
                <span wire:loading wire:target="search">...</span>
            </button>
        </form>
        @error('domain')
            <p class="text-red-500 mt-2 text-sm font-semibold">{{ $message }}</p>
        @enderror

        @if($type === 'transfer')
            <div class="mt-4">
                <label class="block text-slate-600 font-semibold mb-2">{{ __('client/store.domain_epp_code_label') }}</label>
                <input type="text" wire:model.defer="eppCode" class="w-full px-4 py-3 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-lg focus:border-billmora-primary-500 outline-none transition-colors" placeholder="e.g. ABCDEFGHIJK">
                <p class="text-slate-400 text-sm mt-1">{{ __('client/store.domain_epp_code_helper') }}</p>
                @error('eppCode')
                    <p class="text-red-500 mt-1 text-sm font-semibold">{{ $message }}</p>
                @enderror
            </div>
        @endif

        @endif 
    </div>

    @if($searched && $available)
        <div class="bg-billmora-bg p-8 border-2 border-billmora-primary-500 rounded-2xl">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-700 mb-1">{{ $domainName }}</h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-bold">
                        <x-lucide-check-circle class="w-4 h-4" />
                        {{ __('client/store.domain_available') }}
                    </span>
                    @if($checkPrice !== null)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-sm font-bold ml-2">
                            Premium: {{ Currency::format($checkPrice) }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="flex-grow">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">{{ __('client/store.domain_years_label') }}</label>
                        <select wire:model.live="selectedYears" class="w-full md:w-48 px-4 py-2 bg-white border-2 border-billmora-2 rounded-lg font-semibold text-slate-700 outline-none focus:border-billmora-primary-500 cursor-pointer">
                            @foreach($yearOptions as $opt)
                                <option value="{{ $opt['years'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button wire:click="addToCart" class="mt-5 px-6 py-2.5 bg-billmora-primary-500 hover:bg-billmora-primary-600 text-white rounded-lg font-bold transition-colors whitespace-nowrap shadow flex items-center gap-2 cursor-pointer">
                        <x-lucide-shopping-cart class="w-5 h-5" />
                        {{ __('client/store.domain_add_to_cart') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
