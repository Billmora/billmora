@extends('client::layouts.app')

@section('title', 'Store')

@section('body')
<div x-data="orderSummary()" x-init="init()">
    <form action="{{ route('client.checkout.review.initiate') }}" method="POST" class="flex flex-col lg:flex-row gap-5">
        @csrf
        <div class="w-full lg:w-2/3 h-fit grid gap-4">
            <div class="bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <h1 class="text-xl font-semibold text-slate-600">{{ $package->catalog->name }} - {{ $package->name }}</h1>
                <p class="text-slate-500">{!! $package->description !!}</p>
            </div>
            <span class="text-xl text-slate-600 font-semibold">{{ __('client/store.package.billing_cycle') }}</span>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($packagePricesPayload as $p)
                    <label class="group relative cursor-pointer">
                        <input type="radio" name="price_id" value="{{ $p['id'] }}" 
                               x-on:change="selectCycle(@js($p))" class="hidden" 
                               :checked="selectedBillingId == {{ $p['id'] }}">
                        <div class="h-full bg-white p-4 border-2 border-billmora-2 rounded-xl transition-all group-has-[:checked]:border-billmora-primary hover:border-billmora-primary">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 h-4 w-4 rounded-full border-2 border-slate-500 group-has-[:checked]:border-billmora-primary group-has-[:checked]:bg-billmora-primary transition-all"></div>
                                <div class="flex flex-col">
                                    <h4 class="text-sm font-semibold text-slate-600">{{ $p['name'] }}</h4>
                                    <span class="text-sm font-semibold text-slate-500">
                                        {{ Currency::format($p['price']) }} @if($p['setup_fee'] > 0) + {{ Currency::format($p['setup_fee']) }} {{ __('client/store.package.setup_fee') }}@endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            @if(($variants ?? collect())->isNotEmpty())
                <div class="grid gap-4">
                    @foreach($variants as $variant)
                        <template x-if="variantHasAvailableOptions({{ $variant->id }})">
                            <div class="bg-white p-6 border-2 border-billmora-2 rounded-2xl">
                                <h3 class="text-lg font-semibold text-slate-600">{{ $variant->name }}</h3>
                                @includeWhen($variant->type === 'select', 'client::store.catalog.package.variants.select', ['variant' => $variant])
                                @includeWhen($variant->type === 'radio', 'client::store.catalog.package.variants.radio', ['variant' => $variant])
                                @includeWhen($variant->type === 'checkbox', 'client::store.catalog.package.variants.checkbox', ['variant' => $variant])
                                @includeWhen($variant->type === 'slider', 'client::store.catalog.package.variants.slider', ['variant' => $variant])
                            </div>
                        </template>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="w-full lg:w-1/3 h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl space-y-4">
            <h2 class="text-xl font-semibold text-slate-600 mb-4">{{ __('client/store.package.order_summary') }}</h2>
            <div class="grid gap-2">
                <div class="flex justify-between font-semibold text-slate-600">
                    <span>{{ $package->catalog->name }} - {{ $package->name }}</span>
                    <span x-text="cyclePriceFormatted"></span>
                </div>
                <template x-for="row in variantSummaryRows" :key="row.key">
                    <div class="flex justify-between text-slate-500 text-sm font-medium">
                        <span x-text="row.label"></span>
                        <span x-text="row.priceFormatted"></span>
                    </div>
                </template>
                <hr class="border-t-2 border-billmora-2 my-2">
                <div class="flex justify-between font-semibold text-slate-600">
                    <span>{{ __('client/store.package.subtotal') }}</span>
                    <span x-text="subtotalFormatted"></span>
                </div>
                <div class="flex justify-between text-slate-500 font-semibold">
                    <span>{{ __('client/store.package.setup_fee') }}</span>
                    <span x-text="setupFeeFormatted"></span>
                </div>
                <hr class="border-t-2 border-billmora-2 my-2">
                <div class="flex flex-col">
                    <span class="text-slate-600 font-semibold">{{ __('client/store.package.due_today') }}</span>
                    <span class="text-2xl text-billmora-primary font-bold" x-text="totalFormatted"></span>
                </div>
                <template x-if="setupFee > 0">
                    <div class="mt-2 p-3 bg-slate-50 rounded-lg text-xs text-slate-500 border border-slate-100">
                        <p>{{ __('client/store.package.next_billing') }}: <span x-text="subtotalFormatted" class="font-bold text-slate-700"></span></p>
                    </div>
                </template>
            </div>
            <button type="submit" class="w-full bg-billmora-primary hover:bg-billmora-primary-hover p-3 text-white rounded-xl font-semibold transition-all">
                {{ __('client/store.package.checkout') }}
            </button>
        </div>
    </form>
</div>
<script>
function orderSummary() {
    return {
        prices: @json($packagePricesPayload),
        variants: @json($variantsPayload),
        currency: @json($currencyActive->toArray()), 
        labels: {
            setup_fee: @js(__('client/store.package.setup_fee')),
        },
        
        selectedBillingId: null,
        selectedCycleName: '',
        
        cyclePrice: 0, 
        cycleSetupFee: 0,
        cyclePriceFormatted: '',
        
        selectedOptionByVariant: {}, 
        selectedOptionsByVariant: {},
        variantSummaryRows: [],
        
        subtotal: 0, setupFee: 0, total: 0,
        subtotalFormatted: '', setupFeeFormatted: '', totalFormatted: '',

        init() {
            this.variants.forEach(v => {
                if (v.type === 'checkbox') this.selectedOptionsByVariant[v.id] = new Set();
            });
            const firstPrice = this.prices[0];
            if (firstPrice) this.selectCycle(firstPrice, false);

            window.location.search.length > 1 ? this.parseQuery() : this.applyDefaults();
        },

        selectCycle(p, sync = true) {
            this.selectedBillingId = p.id;
            this.selectedCycleName = p.name;
            this.cyclePrice = Number(p.price);
            this.cycleSetupFee = Number(p.setup_fee);
            
            this.cyclePriceFormatted = this.formatCurrency(this.cyclePrice);
            
            this.validateSelections();
            this.applyDefaultsIfEmpty();
            if (sync) this.syncUrl();
            this.recomputeAll();
        },

        recomputeAll() {
            let sub = this.cyclePrice;
            let set = this.cycleSetupFee;
            let rows = [];

            const process = (vId, oId) => {
                const data = this.getOptionPriceData(vId, oId);
                if (!data) return;
                
                const pPrice = Number(data.price);
                const pSetup = Number(data.setup_fee);

                sub += pPrice;
                set += pSetup;

                rows.push({
                    key: `${vId}-${oId}`,
                    label: `${this.getVariant(vId).name}: ${this.getOption(vId, oId).name}`,
                    priceFormatted: this.formatCurrency(pPrice) 
                });
            };

            Object.entries(this.selectedOptionByVariant).forEach(([vId, oId]) => process(vId, oId));
            Object.entries(this.selectedOptionsByVariant).forEach(([vId, s]) => s.forEach(oId => process(vId, oId)));

            this.variantSummaryRows = rows;
            this.subtotal = sub; 
            this.setupFee = set; 
            this.total = sub + set;
            
            this.subtotalFormatted = this.formatCurrency(this.subtotal);
            this.setupFeeFormatted = this.formatCurrency(this.setupFee);
            this.totalFormatted = this.formatCurrency(this.total);
        },

        formatVariantOptionPrice(vId, oId) {
            const d = this.getOptionPriceData(vId, oId);
            if (!d) return '';
            return this.formatWithSetup(Number(d.price), Number(d.setup_fee));
        },
        
        formatWithSetup(price, setupFee) {
            const pStr = this.formatCurrency(price);
            if (setupFee > 0) {
                return `${pStr} + ${this.formatCurrency(setupFee)} ${this.labels.setup_fee}`;
            }
            return pStr;
        },

        formatCurrency(amount) {
            if (amount === null || amount === undefined) return '';
            if (Number(amount) === 0) return 'Free';

            const formatPattern = this.currency.format || '1,234.56';
            let decimals = 2, thousandSep = ',', decimalSep = '.';

            if (!formatPattern.includes('.') && !formatPattern.includes(',') && formatPattern.includes(' ')) {
                 thousandSep = ' '; decimals = 0;
            } else if (formatPattern.endsWith(',234') || formatPattern.endsWith('.234')) {
                 decimals = 0;
                 thousandSep = formatPattern.includes(',') ? ',' : '.';
            } else if (formatPattern === '1,234') {
                 decimals = 0; thousandSep = ',';
            } else if (formatPattern === '1.234') {
                 decimals = 0; thousandSep = '.';
            } else {
                if (formatPattern.includes(',') && formatPattern.indexOf(',') > formatPattern.indexOf('.')) {
                    thousandSep = '.'; decimalSep = ',';
                } else {
                    thousandSep = ','; decimalSep = '.';
                }
            }

            const fixed = Number(amount).toFixed(decimals);
            let [integer, fraction] = fixed.split('.');
            integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSep);
            
            const num = decimals > 0 ? `${integer}${decimalSep}${fraction}` : integer;
            const prefix = this.currency.prefix ? this.currency.prefix + '' : '';
            const suffix = this.currency.suffix ? ' ' + this.currency.suffix : '';

            return `${prefix}${num}${suffix}`.trim();
        },

        getVariant(vId) { return this.variants.find(v => v.id == vId); },
        getOption(vId, oId) { return this.getVariant(vId)?.options.find(o => o.id == oId); },
        getOptionPriceData(vId, oId) { return this.getOption(vId, oId)?.prices_by_name?.[this.selectedCycleName]; },
        variantOptionAvailable(vId, oId) { return !!this.getOptionPriceData(vId, oId); },
        variantHasAvailableOptions(vId) { return this.getAvailableOptions(vId).length > 0; },
        getAvailableOptions(vId) { return this.getVariant(vId)?.options.filter(o => this.variantOptionAvailable(vId, o.id)) || []; },
        getAvailableOptionsForCycle(vId) { return this.getAvailableOptions(vId); },
        
        selectOptionLabel(vId, oId, name) {
            const priceText = this.formatVariantOptionPrice(vId, oId);
            return priceText ? `${name} - ${priceText}` : name;
        },
        sliderTickClass(n, i) {
            if (n <= 1 || i === 0) return 'start-0 text-start';
            if (i === n - 1) return 'end-0 text-end';
            return `left-[${(i / (n - 1)) * 100}%] -translate-x-1/2 text-center`;
        },
        
        isCheckboxSelected(vId, oId) { return this.selectedOptionsByVariant[vId]?.has(Number(oId)); },
        toggleVariantCheckbox(vId, oId, c) { 
            const s = this.selectedOptionsByVariant[vId]; 
            c ? s.add(Number(oId)) : s.delete(Number(oId)); 
            this.recomputeAll(); this.syncUrl(); 
        },
        setVariantRadio(vId, oId) { this.selectedOptionByVariant[vId] = Number(oId); this.recomputeAll(); this.syncUrl(); },
        setVariantSelect(vId, val) { this.selectedOptionByVariant[vId] = Number(val); this.recomputeAll(); this.syncUrl(); },
        setVariantSlider(vId, idx) {
             const opts = this.getAvailableOptions(vId);
             if(opts[idx]) { this.selectedOptionByVariant[vId] = opts[idx].id; this.recomputeAll(); this.syncUrl(); }
        },
        applyDefaults() {
            this.variants.forEach(v => {
                if (v.type !== 'checkbox') {
                    const opts = this.getAvailableOptions(v.id);
                    if (opts.length) this.selectedOptionByVariant[v.id] = opts[0].id;
                }
            });
            this.syncUrl();
            this.recomputeAll();
        },
        applyDefaultsIfEmpty() {
             this.variants.forEach(v => {
                if (v.type !== 'checkbox' && !this.selectedOptionByVariant[v.id]) {
                    const opts = this.getAvailableOptions(v.id);
                    if (opts.length) this.selectedOptionByVariant[v.id] = opts[0].id;
                }
            });
        },
        validateSelections() {
            Object.keys(this.selectedOptionByVariant).forEach(id => {
                if (!this.variantOptionAvailable(id, this.selectedOptionByVariant[id])) delete this.selectedOptionByVariant[id];
            });
            Object.entries(this.selectedOptionsByVariant).forEach(([vId, set]) => {
                set.forEach(oId => { if (!this.variantOptionAvailable(vId, oId)) set.delete(oId); });
            });
        },
        parseQuery() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('billing')) {
                const p = this.prices.find(x => x.id == params.get('billing'));
                if (p) this.selectCycle(p, false);
            }
            this.variants.forEach(v => {
                const val = params.get(`variants[${v.id}]`);
                if (!val) return;
                if (v.type === 'checkbox') {
                    val.split(',').forEach(id => this.variantOptionAvailable(v.id, id) && this.selectedOptionsByVariant[v.id].add(Number(id)));
                } else if (this.variantOptionAvailable(v.id, val)) {
                    this.selectedOptionByVariant[v.id] = Number(val);
                }
            });
            this.applyDefaultsIfEmpty();
            this.recomputeAll();
        },
        syncUrl() {
            const p = new URLSearchParams();
            if (this.selectedBillingId) p.set('billing', this.selectedBillingId);
            Object.entries(this.selectedOptionByVariant).forEach(([k, v]) => p.set(`variants[${k}]`, v));
            Object.entries(this.selectedOptionsByVariant).forEach(([k, v]) => v.size && p.set(`variants[${k}]`, [...v].join(',')));
            history.replaceState({}, '', `${location.pathname}?${p.toString().replace(/%5B/g, '[').replace(/%5D/g, ']')}`);
        }
    };
}
</script>
@endsection