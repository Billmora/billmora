@extends('client::layouts.app')

@section('title', 'Store')

@section('body')
@php
    $fmt = $currencyActive->format;
    $hasComma = str_contains($fmt, ',');
    $hasDot = str_contains($fmt, '.');

    if ($hasComma && $hasDot) {
        $commaFirst = strpos($fmt, ',') < strpos($fmt, '.');
        $thousandSep = $commaFirst ? ',' : '.';
        $decimalSep = $commaFirst ? '.' : ',';
        $decimals = strlen(substr($fmt, strrpos($fmt, $decimalSep) + 1));
    } else {
        $thousandSep = $hasComma ? ',' : '';
        $decimalSep = '.';
        $decimals = $hasDot ? strlen(substr($fmt, strrpos($fmt, '.') + 1)) : 0;
    }
@endphp
<div x-data="orderSummary()" x-init="init()">
    <form action="{{ route('client.checkout.cart.add') }}" method="POST" class="flex flex-col lg:flex-row gap-5">
        @csrf
        <div class="w-full lg:w-2/3 h-fit grid gap-4">
            <div class="bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <h1 class="text-xl font-semibold text-slate-600">
                    {{ $package->catalog->name }} – {{ $package->name }}
                </h1>
                <p class="text-slate-500">{!! $package->description !!}</p>
            </div>
            <div class="bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <span class="text-xl text-slate-600 font-semibold">
                    {{ __('client/store.package.billing_cycle') }}
                </span>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    @foreach ($packagePricesPayload as $p)
                        <label class="relative cursor-pointer">
                            <input
                                type="radio"
                                name="price_id"
                                value="{{ $p['id'] }}"
                                class="hidden"
                                x-on:change="selectCycle({{ json_encode($p) }})"
                                :checked="selectedBillingId == {{ $p['id'] }}"
                            >
                            <div class="h-full bg-white p-4 border-2 rounded-xl transition-all hover:border-billmora-primary"
                                :class="selectedBillingId == {{ $p['id'] }} ? 'border-billmora-primary' : 'border-billmora-2'">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 h-4 w-4 rounded-full border-2 transition-all"
                                        :class="selectedBillingId == {{ $p['id'] }} ? 'border-billmora-primary bg-billmora-primary' : 'border-slate-500'">
                                    </div>
                                    <div class="flex flex-col">
                                        <h4 class="text-sm font-semibold text-slate-600">{{ $p['name'] }}</h4>
                                        <span class="text-sm font-semibold text-slate-500">
                                            {{ Currency::format($p['price']) }}
                                            @if ($p['setup_fee'] > 0)
                                                + {{ Currency::format($p['setup_fee']) }}
                                                {{ __('client/store.package.setup_fee') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            @if (($variants ?? collect())->isNotEmpty())
                <div 
                    x-show="variants.some(v => variantHasAvailableOptions(v.id))"
                    x-cloak
                    class="bg-white p-6 border-2 border-billmora-2 rounded-2xl grid gap-4">
                    @foreach ($variants as $variant)
                        <template x-if="variantHasAvailableOptions({{ $variant['id'] }})">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-600">{{ $variant['name'] }}</h3>
                                @switch($variant['type'])
                                    @case('select')
                                        @include('client::store.catalog.package.variants.select', ['variant' => $variant])
                                        @break
                                    @case('radio')
                                        @include('client::store.catalog.package.variants.radio', ['variant' => $variant])
                                        @break
                                    @case('checkbox')
                                        @include('client::store.catalog.package.variants.checkbox', ['variant' => $variant])
                                        @break
                                    @case('slider')
                                        @include('client::store.catalog.package.variants.slider', ['variant' => $variant])
                                        @break
                                @endswitch
                            </div>
                        </template>
                    @endforeach
                </div>
            @endif
            @if (!empty($checkoutSchema))
                <div class="bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    <span class="text-xl text-slate-600 font-semibold">
                        {{ __('client/store.package.configuration') }}
                    </span>
                    <div class="mt-4 grid gap-4">
                        @foreach ($checkoutSchema as $key => $field)
                            @if (in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                                <x-client::input
                                    name="configuration[{{$key}}]"
                                    label="{{ $field['label'] }}"
                                    helper="{{ $field['helper'] ?? '' }}"
                                    type="{{ $field['type'] }}"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                                    :value="old('configuration.' . $key, $field['default'] ?? '')"
                                />
                            @elseif ($field['type'] === 'textarea')
                                <x-client::textarea
                                    name="configuration[{{$key}}]"
                                    label="{{ $field['label'] }}"
                                    helper="{{ $field['helper'] ?? '' }}"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                                >{{ old('configuration.' . $key, $field['default'] ?? '') }}</x-client::textarea>

                            @elseif ($field['type'] === 'toggle')
                                <x-client::toggle
                                    name="configuration[{{$key}}]"
                                    label="{{ $field['label'] }}"
                                    helper="{{ $field['helper'] ?? '' }}"
                                    :checked="(bool) old('configuration.' . $key, $field['default'] ?? false)"
                                />
                            @elseif ($field['type'] === 'select')
                                <x-client::select
                                    name="configuration[{{$key}}]"
                                    label="{{ $field['label'] }}"
                                    helper="{{ $field['helper'] ?? '' }}"
                                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                                >
                                    @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                                        <option
                                            value="{{ $optValue }}"
                                            @selected(old('configuration.' . $key, $field['default'] ?? '') == $optValue)
                                        >{{ $optLabel }}</option>
                                    @endforeach
                                </x-client::select>
                            @elseif ($field['type'] === 'radio')
                                <x-client::radio.group
                                    name="configuration[{{$key}}]"
                                    label="{{ $field['label'] }}"
                                    helper="{{ $field['helper'] ?? '' }}"
                                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                                >
                                    @foreach ($field['options'] ?? [] as $optVal => $optLabel)
                                        <x-client::radio.option
                                            name="configuration[{{$key}}]"
                                            value="{{ $optVal }}"
                                            label="{{ $optLabel }}"
                                            :checked="old('configuration.' . $key, $field['default'] ?? '') == $optVal"
                                        />
                                    @endforeach
                                </x-client::radio.group>
                            @elseif ($field['type'] === 'checkbox')
                                <x-client::checkbox
                                    name="configuration[{{$key}}]"
                                    label="{{ $field['label'] }}"
                                    helper="{{ $field['helper'] ?? '' }}"
                                    :options="$field['options'] ?? []"
                                    :checked="old('configuration.' . $key, $field['default'] ?? [])"
                                />
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="w-full lg:w-1/3 h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl space-y-4">
            <h2 class="text-xl font-semibold text-slate-600 mb-4">
                {{ __('client/store.package.order_summary') }}
            </h2>
            <div class="grid gap-2">
                <div class="flex justify-between font-semibold text-slate-600">
                    <span>{{ $package->catalog->name }} – {{ $package->name }}</span>
                    <span x-text="cyclePriceFormatted"></span>
                </div>
                <template x-for="row in variantSummaryRows" :key="row.key">
                    <div class="flex justify-between text-slate-500 text-sm font-medium">
                        <span x-text="row.label"></span>
                        <span x-text="row.priceFormatted"></span>
                    </div>
                </template>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between font-semibold text-slate-600">
                <span>{{ __('client/store.package.subtotal') }}</span>
                <span x-text="subtotalFormatted"></span>
            </div>
            <div class="flex justify-between text-slate-500 font-semibold">
                <span>{{ __('client/store.package.setup_fee') }}</span>
                <span x-text="setupFeeFormatted"></span>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex flex-col">
                <span class="text-slate-600 font-semibold">
                    {{ __('client/store.package.due_today') }}
                </span>
                <span class="text-2xl text-billmora-primary font-bold" x-text="totalFormatted"></span>
            </div>
            <template x-if="setupFee > 0">
                <div class="mt-2 p-2 bg-billmora-1 rounded-lg text-sm font-medium text-slate-500 border border-slate-100">
                    <p>
                        {{ __('client/store.package.next_billing') }}:
                        <span class="font-semibold text-slate-600" x-text="subtotalFormatted"></span>
                    </p>
                </div>
            </template>
            <button
                type="submit"
                class="w-full bg-billmora-primary hover:bg-billmora-primary-hover p-3 text-white rounded-xl font-semibold transition-all cursor-pointer"
            >
                {{ __('client/store.package.add_to_cart') }}
            </button>
        </div>
    </form>
</div>
<script>
function orderSummary() {
    return {
        prices: @json($packagePricesPayload),
        variants: @json($variantsPayload),
        currency: {
            prefix: @js($currencyActive->prefix ?? ''),
            suffix: @js($currencyActive->suffix ?? ''),
            decimals: {{ $decimals }},
            thousandSep: @js($thousandSep),
            decimalSep: @js($decimalSep),
        },
        labels: { setupFee: @js(__('client/store.package.setup_fee')) },

        selectedBillingId: null, selectedCycleName: '',
        cyclePrice: 0, cycleSetupFee: 0, cyclePriceFormatted: '',
        selectedOptionByVariant: {}, selectedOptionsByVariant: {},
        variantSummaryRows: [],
        subtotal: 0, setupFee: 0, total: 0,
        subtotalFormatted: '', setupFeeFormatted: '', totalFormatted: '',

        init() {
            this.variants.forEach(v => {
                if (v.type === 'checkbox') this.selectedOptionsByVariant[v.id] = new Set();
            });
            const first = this.prices[0];
            if (first) this.selectCycle(first, false);
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
            let sub = this.cyclePrice, fee = this.cycleSetupFee;
            const rows = [];

            const process = (vId, oId) => {
                const d = this.getOptionPriceData(vId, oId);
                if (!d) return;
                sub += Number(d.price);
                fee += Number(d.setup_fee);
                rows.push({
                    key: `${vId}-${oId}`,
                    label: `${this.getVariant(vId).name}: ${this.getOption(vId, oId).name}`,
                    priceFormatted: this.formatCurrency(Number(d.price)),
                });
            };

            Object.entries(this.selectedOptionByVariant).forEach(([vId, oId]) => process(vId, oId));
            Object.entries(this.selectedOptionsByVariant).forEach(([vId, s]) => s.forEach(oId => process(vId, oId)));

            this.variantSummaryRows = rows;
            this.subtotal = sub;
            this.setupFee = fee;
            this.total = sub + fee;
            this.subtotalFormatted = this.formatCurrency(sub);
            this.setupFeeFormatted = this.formatCurrency(fee);
            this.totalFormatted = this.formatCurrency(sub + fee);
        },

        formatCurrency(amount) {
            if (Number(amount) === 0) return 'Free';
            const { prefix, suffix, decimals, thousandSep, decimalSep } = this.currency;
            const [int, frac] = Number(amount).toFixed(decimals).split('.');
            const formatted   = int.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSep);
            const num = decimals > 0 ? `${formatted}${decimalSep}${frac}` : formatted;
            return [prefix, num, suffix].filter(Boolean).join(' ').trim();
        },

        formatVariantOptionPrice(vId, oId) {
            const d = this.getOptionPriceData(vId, oId);
            if (!d) return '';
            const p = this.formatCurrency(Number(d.price));
            return Number(d.setup_fee) > 0
                ? `${p} + ${this.formatCurrency(Number(d.setup_fee))} ${this.labels.setupFee}`
                : p;
        },

        getVariant(vId) { return this.variants.find(v => v.id == vId); },
        getOption(vId, oId) { return this.getVariant(vId)?.options.find(o => o.id == oId); },
        getOptionPriceData(vId, oId) { return this.getOption(vId, oId)?.prices_by_name?.[this.selectedCycleName]; },
        variantOptionAvailable(vId, oId) { return !!this.getOptionPriceData(vId, oId); },
        variantHasAvailableOptions(vId) { return this.getAvailableOptions(vId).length > 0; },
        getAvailableOptions(vId) { return this.getVariant(vId)?.options.filter(o => this.variantOptionAvailable(vId, o.id)) ?? []; },
        selectOptionLabel(vId, oId, name) { const p = this.formatVariantOptionPrice(vId, oId); return p ? `${name} – ${p}` : name; },

        isCheckboxSelected(vId, oId) { return this.selectedOptionsByVariant[vId]?.has(Number(oId)); },
        toggleVariantCheckbox(vId, oId, checked) {
            const s = this.selectedOptionsByVariant[vId];
            checked ? s.add(Number(oId)) : s.delete(Number(oId));
            this.recomputeAll(); this.syncUrl();
        },
        setVariantRadio(vId, oId) { this.selectedOptionByVariant[vId] = Number(oId);  this.recomputeAll(); this.syncUrl(); },
        setVariantSelect(vId, val) { this.selectedOptionByVariant[vId] = Number(val);  this.recomputeAll(); this.syncUrl(); },
        setVariantSlider(vId, idx) {
            const opts = this.getAvailableOptions(vId);
            if (opts[idx]) this.selectedOptionByVariant[vId] = opts[idx].id;
            this.recomputeAll(); this.syncUrl();
        },

        applyDefaults() {
            this.variants.forEach(v => {
                if (v.type !== 'checkbox') {
                    const opts = this.getAvailableOptions(v.id);
                    if (opts.length) this.selectedOptionByVariant[v.id] = opts[0].id;
                }
            });
            this.syncUrl(); this.recomputeAll();
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
                if (!this.variantOptionAvailable(id, this.selectedOptionByVariant[id]))
                    delete this.selectedOptionByVariant[id];
            });
            Object.entries(this.selectedOptionsByVariant).forEach(([vId, set]) => {
                set.forEach(oId => { if (!this.variantOptionAvailable(vId, oId)) set.delete(oId); });
            });
        },
        parseQuery() {
            const params = new URLSearchParams(window.location.search);
            const billing = params.get('billing');
            if (billing) { const p = this.prices.find(x => x.id == billing); if (p) this.selectCycle(p, false); }
            this.variants.forEach(v => {
                const val = params.get(`variants[${v.id}]`);
                if (!val) return;
                if (v.type === 'checkbox')
                    decodeURIComponent(val).split(',').forEach(id => { if (this.variantOptionAvailable(v.id, id)) this.selectedOptionsByVariant[v.id].add(Number(id)); });
                else if (this.variantOptionAvailable(v.id, val))
                    this.selectedOptionByVariant[v.id] = Number(val);
            });
            this.applyDefaultsIfEmpty(); this.recomputeAll();
        },
        syncUrl() {
            const p = new URLSearchParams();
            if (this.selectedBillingId) p.set('billing', this.selectedBillingId);
            Object.entries(this.selectedOptionByVariant).forEach(([k, v]) => p.set(`variants[${k}]`, v));
            Object.entries(this.selectedOptionsByVariant).forEach(([k, v]) => { if (v.size) p.set(`variants[${k}]`, [...v].join(',')); });

            const queryString = p.toString().replace(/%5B/g, '[').replace(/%5D/g, ']').replace(/%2C/g, ','); 
            history.replaceState(null, '', `${location.pathname}?${queryString}`);
        },
    };
}
</script>
@endsection