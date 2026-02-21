@extends('admin::layouts.app')

@section('title', "Service Edit - {$service->name}")

@section('body')
<div class="flex flex-col-reverse lg:flex-row gap-5">
    <form action="{{ route('admin.services.update', ['service' => $service->id]) }}" method="POST" x-data="serviceForm()" class="w-full lg:w-5/7 flex flex-col gap-5">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <div class="w-full">
                <label for="user_id" class="flex text-slate-600 font-semibold mb-1">
                    {{ __('admin/services.user_label') }}
                </label>
                <a href="{{ route('admin.users.summary', ['id' => $service->user_id]) }}" class="relative inline-block max-w-150 w-full group" target="_blank">
                    <input type="text" name="user_id" id="user_id" value="{{ $service->user->email }}" class="w-full px-3 py-2.25 bg-billmora-1 placeholder:text-gray-400 border-2 border-billmora-2 rounded-xl cursor-not-allowed" disabled>
                    <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                        <button type="button" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">
                            {{ __('admin/services.go_to_user') }}
                        </button>
                    </div>
                </a>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('admin/services.user_helper') }}
                </p>
            </div>
            <x-admin::input 
                name="service_name"
                label="{{ __('admin/services.name_label') }}"
                helper="{{ __('admin/services.name_helper') }}"
                value="{{ $service->name }}"
                required
                disabled
            />
            <x-admin::select 
                name="service_currency"
                label="{{ __('admin/services.currency_label') }}"
                helper="{{ __('admin/services.currency_helper') }}"
                required
                x-model="selectedCurrency"
            >
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->code }}">{{ $currency->code }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::select
                name="service_status"
                label="{{ __('admin/services.status_label') }}"
                helper="{{ __('admin/services.status_helper') }}"
                required
            >
                @foreach(['pending', 'active', 'suspended', 'terminated', 'cancelled'] as $status)
                    <option value="{{ $status }}" {{ old('service_status', $service->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::input 
                name="service_next_due_date" 
                label="{{ __('admin/services.expires_label') }}"
                helper="{{ __('admin/services.expires_helper') }}"
                type="date"
                value="{{ old('service_next_due_date', $service->next_due_date?->format('Y-m-d')) }}"
            />
            <x-admin::toggle 
                name="service_recalculate_price"
                label="{{ __('admin/services.recalculate_label') }}"
                helper="{{ __('admin/services.recalculate_helper') }}"
            />
            <x-admin::input 
                name="service_price" 
                label="{{ __('admin/services.price_label') }}"
                helper="{{ __('admin/services.price_helper') }}"
                type="number" 
                step="0.01"
                value="{{ old('service_price', $service->price) }}"
            />
            <x-admin::input 
                name="service_setup_fee" 
                label="{{ __('admin/services.setup_fee_label') }}"
                helper="{{ __('admin/services.setup_fee_helper') }}"
                type="number" 
                step="0.01"
                value="{{ old('service_setup_fee', $service->setup_fee) }}"
            />
        </div>
        <div>
            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/services.package_configuration_label') }}</h4>
            <span class="text-slate-500">{{ __('admin/services.package_configuration_helper') }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::select 
                name="package_id"
                label="{{ __('admin/services.package_label') }}"
                helper="{{ __('admin/services.package_helper') }}"
                required
                x-model="selectedPackage"
                x-on:disabled="!selectedCurrency"
            >
                <template x-for="pkg in availablePackages" :key="pkg.id">
                    <option :value="pkg.id" :selected="pkg.id == selectedPackage" x-text="pkg.catalog_name + ' - ' + pkg.name"></option>
                </template>
            </x-admin::select>
            <x-admin::select 
                name="package_price_id"
                label="{{ __('admin/services.billing_cycle_label') }}"
                helper="{{ __('admin/services.billing_cycle_helper') }}"
                required
                x-model="selectedPrice"
                x-on:disabled="!selectedPackage"
            >
                <template x-for="price in availablePrices" :key="price.id">
                    <option :value="price.id" :selected="price.id == selectedPrice" x-text="price.name"></option>
                </template>
            </x-admin::select>
        </div>
        <template x-if="currentVariants.length > 0 && hasAnyAvailableOptions()">
            <div>
                <div class="mb-2">
                    <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/services.variant_option_label') }}</h4>
                    <span class="text-slate-500">{{ __('admin/services.variant_option_helper') }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    @foreach ($packagesPayload as $pkgData)
                        @foreach ($pkgData['currencies'] ?? [] as $currencyCode => $currencyData)
                            @foreach ($currencyData['variants'] ?? [] as $variant)
                                <template x-if="
                                    selectedPackage == {{ $pkgData['id'] }} &&
                                    selectedCurrency == '{{ $currencyCode }}' &&
                                    getFilteredOptions({{ json_encode($variant) }}).length > 0
                                ">
                                    <div>
                                        @switch($variant['type'])
                                            @case('select')
                                                <x-admin::select
                                                    name="variant_selections[{{ $variant['id'] }}]"
                                                    label="{{ $variant['name'] }}"
                                                    required
                                                >
                                                    @foreach ($variant['options'] as $option)
                                                        <option
                                                            value="{{ $option['id'] }}"
                                                            x-show="getFilteredOptions({{ json_encode($variant) }}).some(o => o.id == {{ $option['id'] }})"
                                                            :selected="selections[{{ $variant['id'] }}] == {{ $option['id'] }}"
                                                        >{{ $option['name'] }}</option>
                                                    @endforeach
                                                </x-admin::select>
                                                @break
                                            @case('radio')
                                                <x-admin::radio.group
                                                    name="variant_selections[{{ $variant['id'] }}]"
                                                    label="{{ $variant['name'] }}"
                                                    required
                                                >
                                                    @foreach ($variant['options'] as $option)
                                                        <x-admin::radio.option
                                                            name="variant_selections[{{ $variant['id'] }}]"
                                                            label="{{ $option['name'] }}"
                                                            value="{{ $option['id'] }}"
                                                            x-show="getFilteredOptions({{ json_encode($variant) }}).some(o => o.id == {{ $option['id'] }})"
                                                            :checked="old('variant_selections.' . $variant['id'], $service->variant_selections[$variant['id']][0] ?? '') == $option['id']"
                                                        />
                                                    @endforeach
                                                </x-admin::radio.group>
                                                @break
                                            @case('checkbox')
                                                <x-admin::checkbox
                                                    name="variant_selections[{{ $variant['id'] }}]"
                                                    label="{{ $variant['name'] }}"
                                                    :options="collect($variant['options'])->pluck('name', 'id')->toArray()"
                                                    :checked="old('variant_selections.' . $variant['id'], $service->variant_selections[$variant['id']] ?? [])"
                                                />
                                                @break
                                            @case('slider')
                                                <x-admin::slider
                                                    name="variant_selections[{{ $variant['id'] }}]"
                                                    label="{{ $variant['name'] }}"
                                                    :options="collect($variant['options'])->map(fn($o) => [
                                                        'value' => $o['id'],
                                                        'title' => $o['name']
                                                    ])->toArray()"
                                                    value="{{ old('variant_selections.' . $variant['id'], $service->variant_selections[$variant['id']][0] ?? null) }}"
                                                    required
                                                />
                                                @break
                                        @endswitch
                                    </div>
                                </template>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
            </div>
        </template>
        @if (!empty($schemasPayload))
            @foreach ($schemasPayload as $packageId => $schema)
                <template x-if="Number(selectedPackage) === {{ $packageId }}">
                    <div class="space-y-2">
                        <div>
                            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/services.additional_configuration_label') }}</h4>
                            <span class="text-slate-500">{{ __('admin/services.additional_configuration_helper') }}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                            @foreach ($schema as $key => $field)
                                @if (in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                                    <x-admin::input
                                        name="configuration[{{ $key }}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        type="{{ $field['type'] }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        :required="str_contains($field['rules'] ?? '', 'required')"
                                        :value="old('configuration.' . $key, $service->configuration[$key] ?? '')"
                                    />
                                @elseif ($field['type'] === 'textarea')
                                    <x-admin::textarea
                                        name="configuration[{{ $key }}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        :required="str_contains($field['rules'] ?? '', 'required')"
                                    >{{ old('configuration.' . $key, $service->configuration[$key] ?? '') }}</x-admin::textarea>
                                @elseif ($field['type'] === 'toggle')
                                    <x-admin::toggle
                                        name="configuration[{{$key}}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        :checked="(bool) old('configuration.' . $key, $service->configuration[$key] ?? false)"
                                    />
                                @elseif ($field['type'] === 'select')
                                    <x-admin::select
                                        name="configuration[{{ $key }}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        :required="str_contains($field['rules'] ?? '', 'required')"
                                    >
                                        @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                                            <option
                                                value="{{ $optValue }}"
                                                @selected(old('configuration.' . $key,  $service->configuration[$key] ?? '') == $optValue)
                                            >
                                                {{ $optLabel }}
                                            </option>
                                        @endforeach
                                    </x-admin::select>
                                @elseif ($field['type'] === 'radio')
                                    <x-admin::radio.group
                                        name="configuration[{{ $key }}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        :required="str_contains($field['rules'] ?? '', 'required')"
                                    >
                                        @foreach ($field['options'] ?? [] as $optVal => $optLabel)
                                            <x-admin::radio.option
                                                name="configuration[{{ $key }}]"
                                                value="{{ $optVal }}"
                                                label="{{ $optLabel }}"
                                                :checked="old('configuration.' . $key, $service->configuration[$key] ?? '') == $optVal"
                                            />
                                        @endforeach
                                    </x-admin::radio.group>
                                @elseif ($field['type'] === 'checkbox')
                                    <x-admin::checkbox
                                        name="configuration[{{ $key }}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        :options="$field['options'] ?? []"
                                        :checked="old('configuration.' . $key, $service->configuration[$key] ?? [])"
                                    />
                                @endif
                            @endforeach
                        </div>
                    </div>
                </template>
            @endforeach
        @endif
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.services') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.update') }}
            </button>
        </div>
    </form>
    <div class="w-full lg:w-2/7 h-fit grid">
        @include('admin::services._action_group')
    </div>
</div>
<script>
function serviceForm() {
    return {
        selectedCurrency: '{{ old("service_currency", $service->currency) }}',
        selectedPackage: '{{ old("package_id", $service->package_id) }}',
        selectedPrice: '{{ old("package_price_id", $service->package_price_id) }}',
        
        selections: @json(old('variant_selections', $service->variant_selections ?? (object)[])),
        
        selectedPriceName: '',
        
        availablePackages: [],
        availablePrices: [],
        currentVariants: [],
        
        packages: @json($packagesPayload),
        
        init() {
            this.sanitizeSelections();

            this.$watch('selectedCurrency', (val, old) => { if(val && val !== old) this.onCurrencyChange(); });
            this.$watch('selectedPackage', (val, old) => { if(val && val !== old) this.onPackageChange(); });
            this.$watch('selectedPrice', (val, old) => { if(val && val !== old) this.onPriceChange(); });

            this.$nextTick(() => {
                this.initializeFromExisting();
            });
        },

        sanitizeSelections() {
            for (const key in this.selections) {
                if (Array.isArray(this.selections[key])) {
                    this.selections[key] = this.selections[key].map(id => parseInt(id));
                } else if (this.selections[key] !== null && this.selections[key] !== '') {
                    this.selections[key] = parseInt(this.selections[key]);
                }
            }
        },

        initializeFromExisting() {
            this.availablePackages = this.packages.filter(pkg => pkg.currencies[this.selectedCurrency] !== undefined);
            
            if (this.selectedPackage) {
                const pkg = this.packages.find(p => p.id == this.selectedPackage);
                if (pkg && pkg.currencies[this.selectedCurrency]) {
                    this.availablePrices = pkg.currencies[this.selectedCurrency].prices;
                    this.currentVariants = pkg.currencies[this.selectedCurrency].variants;
                    
                    const currentPrice = this.availablePrices.find(p => p.id == this.selectedPrice);
                    
                    if (this.availablePrices.length > 0) {
                        if (!this.selectedPrice || !currentPrice) {
                            this.selectedPrice = this.availablePrices[0].id;
                            this.selectedPriceName = this.availablePrices[0].name;
                        } else {
                            this.selectedPriceName = currentPrice.name;
                        }
                    }
                    
                    this.applyVariantDefaults();
                }
            } else {
                this.availablePrices = [];
                this.currentVariants = [];
            }
        },

        onCurrencyChange() {
            if (!this.selectedCurrency) {
                this.availablePackages = [];
                this.availablePrices = [];
                return;
            }
            this.selectedPackage = ''; 
            this.selectedPrice = '';
            this.selectedPriceName = '';
            
            this.availablePrices = []; 
            this.currentVariants = [];
            this.selections = {}; 

            this.availablePackages = this.packages.filter(pkg => pkg.currencies[this.selectedCurrency] !== undefined);
        },

        onPackageChange() {
            if (!this.selectedPackage) {
                this.availablePrices = [];
                this.currentVariants = [];
                return;
            }
            
            const pkg = this.packages.find(p => p.id == this.selectedPackage);
            if (pkg && pkg.currencies[this.selectedCurrency]) {
                this.availablePrices = pkg.currencies[this.selectedCurrency].prices;
                this.currentVariants = pkg.currencies[this.selectedCurrency].variants;
                
                if (this.availablePrices.length > 0) {
                    this.selectedPrice = this.availablePrices[0].id;
                    this.selectedPriceName = this.availablePrices[0].name;
                }
                this.selections = {};
                this.applyVariantDefaults();
            }
        },

        onPriceChange() {
            if (!this.selectedPrice) {
                this.selectedPriceName = '';
                return;
            }
            const price = this.availablePrices.find(p => p.id == this.selectedPrice);
            this.selectedPriceName = price ? price.name : '';

            const pkg = this.packages.find(p => p.id == this.selectedPackage);
            if (pkg && pkg.currencies[this.selectedCurrency]) {
                this.currentVariants = pkg.currencies[this.selectedCurrency].variants;
                this.applyVariantDefaults();
            }
        },

        applyVariantDefaults() {
            this.currentVariants.forEach(variant => {
                if (variant.type === 'checkbox') {
                    if (this.selections[variant.id] === undefined || this.selections[variant.id] === null) {
                        this.selections[variant.id] = [];
                    } else if (!Array.isArray(this.selections[variant.id])) {
                         let val = this.selections[variant.id];
                         this.selections[variant.id] = val !== '' ? [parseInt(val)] : [];
                    }
                    return;
                }

                if (this.selections[variant.id] !== undefined && this.selections[variant.id] !== null && this.selections[variant.id] !== '') {
                    return;
                }
                const opts = this.getFilteredOptions(variant);
                if (opts.length > 0) {
                    this.selections[variant.id] = opts[0].id;
                }
            });
        },

        hasAnyAvailableOptions() {
            if (!this.selectedPriceName) return false;
            return this.currentVariants.some(variant => this.getFilteredOptions(variant).length > 0);
        },

        getFilteredOptions(variant) {
            if (!this.selectedPriceName) return [];
            return (variant.options || []).filter(option => {
                return option.prices_by_name && option.prices_by_name[this.selectedPriceName] !== undefined;
            });
        }
    }
}
</script>
@endsection