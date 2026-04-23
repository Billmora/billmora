<div class="flex flex-col lg:flex-row gap-5">
<div class="w-full lg:w-2/3 h-fit grid gap-4">
    <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
        <h1 class="text-2xl font-bold text-slate-700 mb-2">{{ __('common.configure') }} - {{ $domainName }}</h1>
        <p class="text-slate-500">{{ __('client/store.domain_configure_helper') ?? 'Please configure your domain registration below.' }}</p>
    </div>

    <form wire:submit.prevent="addToCart" id="domainConfigureForm" class="flex flex-col gap-4">
        
        <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
            <h2 class="text-xl text-slate-600 font-semibold mb-4">{{ __('client/store.domain_years_label') }}</h2>
            <x-client::select 
                name="selectedYears" 
                wire:model.live="selectedYears"
                required
            >
                @foreach($yearOptions as $opt)
                    <option value="{{ $opt['years'] }}">{{ $opt['label'] }}</option>
                @endforeach
            </x-client::select>
        </div>

        @if($type === 'transfer')
        <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
            <x-client::input 
                name="eppCode"
                wire:model.defer="eppCode"
                label="{{ __('client/store.domain_epp_code_label') }}"
                helper="{{ __('client/store.domain_epp_code_helper') }}"
                placeholder="e.g. ABCDEFGHIJK"
            />
        </div>
        @endif

        <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
            <h2 class="text-xl text-slate-600 font-semibold mb-1">Nameservers</h2>
            <p class="text-slate-500 text-sm mb-4">If you want to use custom nameservers then enter them below. By default, new domains will use our nameservers for hosting on our network.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @for ($i = 1; $i <= 5; $i++)
                    <x-client::input 
                        name="nameservers.{{ $i - 1 }}"
                        wire:model.defer="nameservers.{{ $i - 1 }}"
                        label="Nameserver {{ $i }}"
                        placeholder="ns{{ $i }}.example.com"
                        :value="$nameservers[$i - 1] ?? ''"
                    />
                @endfor
            </div>
        </div>

    </form>
</div>

<div class="w-full lg:w-1/3 h-fit bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl space-y-4 relative overflow-hidden">
    <h2 class="text-xl font-semibold text-slate-600 mb-4">
        {{ __('client/store.package.order_summary') }}
    </h2>
    
    <div class="grid gap-2">
        @php
            $basePrice = $type === 'register' ? $tldPrice->register_price : $tldPrice->transfer_price;
            $totalPrice = $basePrice * (int)$selectedYears;
        @endphp
        <div class="flex justify-between font-semibold text-slate-600">
            <span>{{ $domainName }}</span>
            <span>{{ \App\Facades\Currency::format($totalPrice) }}</span>
        </div>
        <div class="flex justify-between text-slate-500 text-sm font-medium">
            <span>{{ $selectedYears }} {{ $selectedYears > 1 ? 'Years' : 'Year' }} {{ ucfirst($type) }}</span>
            <span>-</span>
        </div>
    </div>
    
    <hr class="border-t-2 border-billmora-2">
    
    <div class="flex justify-between font-semibold text-slate-600">
        <span>{{ __('client/store.package.subtotal') }}</span>
        <span>{{ \App\Facades\Currency::format($totalPrice) }}</span>
    </div>
    
    <hr class="border-t-2 border-billmora-2">
    
    <div class="flex flex-col">
        <span class="text-slate-600 font-semibold">
            {{ __('client/store.package.due_today') }}
        </span>
        <span class="text-2xl text-billmora-primary-500 font-bold">
            {{ \App\Facades\Currency::format($totalPrice) }}
        </span>
    </div>
    
    <button type="submit" form="domainConfigureForm" class="mt-4 w-full bg-billmora-primary-500 hover:bg-billmora-primary-600 p-3 text-white rounded-lg font-semibold transition-all cursor-pointer">
        {{ __('client/store.domain_add_to_cart') }}
    </button>
</div>
</div>
