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
                        <input
                            type="radio"
                            name="price_id"
                            value="{{ $p['id'] }}"
                            x-on:change="selectCycle(@js($p))"
                            class="hidden"
                            :checked="selectedBillingId == {{ $p['id'] }}"
                        >
                        <div class="h-full bg-white p-4 border-2 border-billmora-2
                                rounded-xl transition-all
                                group-has-[:checked]:border-billmora-primary
                                hover:border-billmora-primary">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 h-4 w-4 rounded-full border-2 border-slate-500
                                        group-has-[:checked]:border-billmora-primary
                                        group-has-[:checked]:bg-billmora-primary
                                        transition-all"></div>
                                <div class="flex flex-col">
                                    <h4 class="text-sm font-semibold text-slate-600">{{ $p['name'] }}</h4>
                                    <span class="text-sm font-semibold text-slate-500">{{ $p['price_f'] }}</span>
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
            <div class="grid">
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-600 font-semibold text-start break-all">{{ $package->catalog->name }} - {{ $package->name }}</span>
                    <span class="text-slate-600 font-semibold text-end break-all"><span x-text="cyclePriceFormatted"></span></span>
                </div>
                <template x-for="row in variantSummaryRows" :key="row.key">
                    <div class="flex gap-3 justify-between">
                        <span class="text-slate-500 font-semibold text-start break-all" x-text="row.label"></span>
                        <span class="text-slate-500 font-semibold text-end break-all" x-text="row.priceFormatted"></span>
                    </div>
                </template>
                <hr class="border-t-2 border-billmora-2 my-4">
                <span class="text-slate-600 font-semibold text-start text-md break-all">{{ __('client/store.package.billing_cycle') }}</span>
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-500 font-semibold text-start break-all" x-text="cycleName"></span>
                    <span class="text-slate-500 font-semibold text-end break-all" x-text="cyclePriceFormatted"></span>
                </div>
                <hr class="border-t-2 border-billmora-2 my-4">
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-500 font-semibold text-start break-all">{{ __('client/store.package.setup_fee') }}</span>
                    <span class="text-slate-500 font-semibold text-end break-all" x-text="setupFeeFormatted"></span>
                </div>
                <hr class="border-t-2 border-billmora-2 my-4">
                <div class="flex flex-col">
                    <span class="text-slate-600 font-semibold break-all">{{ __('client/store.package.due_today') }}</span>
                    <span class="text-xl text-slate-600 font-semibold break-all" x-text="totalFormatted"></span>
                </div>
            </div>
            <button type="submit" class="w-full bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('client/store.package.checkout') }}
            </button>
        </div>
    </form>
</div>
<script>
function orderSummary() {
    return {
        packagePrices: @json($packagePricesPayload),
        variants: @json($variantsPayload),
        currency: @js(['prefix' => $currencyActive->prefix ?? '', 'suffix' => $currencyActive->suffix ?? '', 'format' => $currencyActive->format ?? '1,234.56']),
        
        selectedBillingId: null,
        selectedCycleName: '',
        cycleName: '',
        cyclePrice: 0,
        setupFee: 0,
        cyclePriceFormatted: '',
        setupFeeFormatted: '',
        selectedOptionByVariant: {},
        selectedOptionsByVariant: {},
        variantSummaryRows: [],
        total: 0,
        totalFormatted: '',

        init() {
            this.initCheckboxVariants();
            const defaultPrice = this.packagePrices[0];
            if (defaultPrice) this.selectCycle(defaultPrice, false);

            window.location.search.length > 1 ? this.parseQuery() : this.applyDefaults();
            this.recomputeAll();
        },

        initCheckboxVariants() {
            this.variants.forEach(v => {
                if (v.type === 'checkbox') this.selectedOptionsByVariant[v.id] = new Set();
            });
        },

        applyDefaults() {
            this.variants.forEach(v => {
                const opts = this.getAvailableOptionsForCycle(v.id);
                if (opts.length && v.type !== 'checkbox') {
                    this.selectedOptionByVariant[v.id] = opts[0].id;
                }
            });
            this.syncUrl();
        },

        parseQuery() {
            const params = new URLSearchParams(window.location.search);
            let dirty = false;

            // Parse billing
            const billingId = params.get('billing');
            if (billingId) {
                const price = this.packagePrices.find(x => Number(x.id) === Number(billingId));
                if (price) {
                    this.selectCycle(price, false);
                } else {
                    this.resetToDefault();
                    dirty = true;
                }
            }

            // Parse variants
            params.forEach((val, key) => {
                const match = key.match(/^variants\[(\d+)\]$/);
                if (!match) return;

                const variantId = Number(match[1]);
                const variant = this.findVariant(variantId);
                if (!variant) {
                    dirty = true;
                    return;
                }

                if (val.includes(',')) {
                    dirty = !this.parseCheckboxVariant(variantId, val) || dirty;
                } else {
                    dirty = !this.parseSingleVariant(variantId, val) || dirty;
                }
            });

            this.cleanupSelectionsForCycle();
            if (dirty) this.syncUrl();
        },

        parseCheckboxVariant(variantId, csvValue) {
            if (!this.selectedOptionsByVariant[variantId]) {
                this.selectedOptionsByVariant[variantId] = new Set();
            }

            const ids = csvValue.split(',').map(x => Number(x)).filter(Boolean);
            let anyValid = false;

            ids.forEach(optionId => {
                if (this.variantOptionAvailable(variantId, optionId)) {
                    this.selectedOptionsByVariant[variantId].add(optionId);
                    anyValid = true;
                }
            });

            return anyValid;
        },

        parseSingleVariant(variantId, value) {
            const optionId = Number(value);
            if (this.variantOptionAvailable(variantId, optionId)) {
                this.selectedOptionByVariant[variantId] = optionId;
                return true;
            }
            return false;
        },

        resetToDefault() {
            const defaultPrice = this.packagePrices[0];
            if (defaultPrice) this.selectCycle(defaultPrice, false);
            
            this.selectedOptionByVariant = {};
            this.selectedOptionsByVariant = {};
            this.initCheckboxVariants();
        },

        syncUrl() {
            const params = new URLSearchParams();
            if (this.selectedBillingId) params.set('billing', this.selectedBillingId);

            Object.entries(this.selectedOptionByVariant).forEach(([vId, oId]) => {
                if (oId) params.set(`variants[${vId}]`, oId);
            });

            Object.entries(this.selectedOptionsByVariant).forEach(([vId, set]) => {
                if (set?.size) params.set(`variants[${vId}]`, Array.from(set).join(','));
            });

            const url = new URL(window.location.href);
            url.search = params.toString().replaceAll('%5B', '[').replaceAll('%5D', ']').replaceAll('%2C', ',');
            history.replaceState({}, '', url);
        },

        selectCycle(price, doSync = true) {
            Object.assign(this, {
                selectedBillingId: Number(price.id),
                selectedCycleName: price.name,
                cycleName: price.name,
                cyclePrice: Number(price.price) || 0,
                setupFee: Number(price.setup_fee) || 0,
                cyclePriceFormatted: price.price_f,
                setupFeeFormatted: price.setup_fee_f
            });

            this.applyDefaultSelectionsForCycle();
            this.recomputeAll();
            if (doSync) this.syncUrl();
        },

        applyDefaultSelectionsForCycle() {
            // Remove invalid single selections
            Object.keys(this.selectedOptionByVariant).forEach(vId => {
                if (!this.variantOptionAvailable(Number(vId), this.selectedOptionByVariant[vId])) {
                    delete this.selectedOptionByVariant[vId];
                }
            });

            // Apply defaults for empty variants
            this.variants.forEach(v => {
                const opts = this.getAvailableOptionsForCycle(v.id);
                
                if (!opts.length) {
                    delete this.selectedOptionByVariant[v.id];
                    this.selectedOptionsByVariant[v.id]?.clear?.();
                    return;
                }

                if (v.type === 'checkbox') {
                    if (!this.selectedOptionsByVariant[v.id]) {
                        this.selectedOptionsByVariant[v.id] = new Set();
                    }
                    this.selectedOptionsByVariant[v.id].clear();
                } else if (!this.selectedOptionByVariant[v.id]) {
                    this.selectedOptionByVariant[v.id] = opts[0].id;
                }
            });
        },

        cleanupSelectionsForCycle() {
            // Clean single selections
            Object.keys(this.selectedOptionByVariant).forEach(vId => {
                if (!this.variantOptionAvailable(Number(vId), this.selectedOptionByVariant[vId])) {
                    delete this.selectedOptionByVariant[vId];
                }
            });

            // Clean checkbox selections
            Object.entries(this.selectedOptionsByVariant).forEach(([vId, set]) => {
                if (!set) return;
                Array.from(set).forEach(oId => {
                    if (!this.variantOptionAvailable(Number(vId), oId)) {
                        set.delete(oId);
                    }
                });
            });
        },

        findVariant(variantId) {
            return this.variants.find(v => Number(v.id) === Number(variantId));
        },

        findOption(variantId, optionId) {
            const variant = this.findVariant(variantId);
            return variant?.options?.find(o => Number(o.id) === Number(optionId));
        },

        getOptionPriceForSelectedCycle(variantId, optionId) {
            const option = this.findOption(variantId, optionId);
            return option?.p?.[this.selectedCycleName] || null;
        },

        variantOptionAvailable(variantId, optionId) {
            return !!this.getOptionPriceForSelectedCycle(variantId, optionId);
        },

        variantHasAvailableOptions(variantId) {
            const variant = this.findVariant(variantId);
            return variant?.options?.some(o => this.variantOptionAvailable(variantId, o.id)) || false;
        },

        getAvailableOptionsForCycle(variantId) {
            const variant = this.findVariant(variantId);
            return variant?.options?.filter(o => this.variantOptionAvailable(variantId, o.id)) || [];
        },

        formatVariantOptionPrice(variantId, optionId) {
            return this.getOptionPriceForSelectedCycle(variantId, optionId)?.total_f || '';
        },

        selectOptionLabel(variantId, optionId, optionName) {
            const price = this.getOptionPriceForSelectedCycle(variantId, optionId);
            return price?.total_f ? `${optionName} - ${price.total_f}` : optionName;
        },

        isCheckboxSelected(variantId, optionId) {
            return this.selectedOptionsByVariant[variantId]?.has(Number(optionId)) || false;
        },

        setVariantRadio(variantId, optionId) {
            this.selectedOptionByVariant[variantId] = Number(optionId);
            this.recomputeAll();
            this.syncUrl();
        },

        setVariantSelect(variantId, optionId) {
            optionId ? this.selectedOptionByVariant[variantId] = Number(optionId) : delete this.selectedOptionByVariant[variantId];
            this.recomputeAll();
            this.syncUrl();
        },

        toggleVariantCheckbox(variantId, optionId, checked) {
            if (!this.selectedOptionsByVariant[variantId]) {
                this.selectedOptionsByVariant[variantId] = new Set();
            }
            checked ? this.selectedOptionsByVariant[variantId].add(Number(optionId)) : this.selectedOptionsByVariant[variantId].delete(Number(optionId));
            this.recomputeAll();
            this.syncUrl();
        },

        sliderOptionId(variantId, idx) {
            const opts = this.getAvailableOptionsForCycle(variantId);
            return opts[idx]?.id || '';
        },

        setVariantSlider(variantId, idx) {
            const optId = this.sliderOptionId(variantId, idx);
            optId ? this.selectedOptionByVariant[variantId] = Number(optId) : delete this.selectedOptionByVariant[variantId];
            this.recomputeAll();
            this.syncUrl();
        },

        sliderTickClass(n, i) {
            if (n <= 1 || i === 0) return 'start-0 text-start';
            if (i === n - 1) return 'end-0 text-end';
            const percent = (i / (n - 1)) * 100;
            return `left-[${percent}%] -translate-x-1/2 text-center`;
        },

        recomputeAll() {
            const base = this.cyclePrice + this.setupFee;
            let variantTotal = 0;
            const rows = [];

            // Single choice variants
            Object.entries(this.selectedOptionByVariant).forEach(([vId, oId]) => {
                const variant = this.findVariant(vId);
                const option = this.findOption(vId, oId);
                const price = this.getOptionPriceForSelectedCycle(vId, oId);

                if (!price) {
                    delete this.selectedOptionByVariant[vId];
                    return;
                }

                variantTotal += Number(price.total) || 0;
                rows.push({
                    key: `v-${vId}`,
                    label: `${variant?.name ?? 'Variant'}: ${option?.name ?? ''}`,
                    priceFormatted: price.total_f || ''
                });
            });

            // Checkbox variants
            Object.entries(this.selectedOptionsByVariant).forEach(([vId, set]) => {
                if (!set?.size) return;

                set.forEach(oId => {
                    const variant = this.findVariant(vId);
                    const option = this.findOption(vId, oId);
                    const price = this.getOptionPriceForSelectedCycle(vId, oId);

                    if (!price) {
                        set.delete(oId);
                        return;
                    }

                    variantTotal += Number(price.total) || 0;
                    rows.push({
                        key: `v-${vId}-${oId}`,
                        label: `${variant?.name ?? 'Variant'}: ${option?.name ?? ''}`,
                        priceFormatted: price.total_f || ''
                    });
                });
            });

            this.variantSummaryRows = rows;
            this.total = base + variantTotal;
            this.totalFormatted = this.total === 0 ? 'Free' : this.formatCurrency(this.total);
        },

        formatCurrency(amount) {
            const formats = {
                '1,234.56': { d: 2, dec: '.', th: ',' },
                '1.234,56': { d: 2, dec: ',', th: '.' },
                '1,234': { d: 0, dec: '.', th: ',' }
            };

            const cfg = formats[this.currency.format] || formats['1,234.56'];
            const fixed = Number(amount).toFixed(cfg.d);
            let [integer, fraction] = fixed.split('.');
            
            integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, cfg.th);
            const num = cfg.d ? `${integer}${cfg.dec}${fraction}` : integer;
            
            return `${this.currency.prefix}${num}${this.currency.suffix ? ` ${this.currency.suffix}` : ''}`.trim();
        }
    };
}
</script>
@endsection