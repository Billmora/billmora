@extends('admin::layouts.app')

@section('title', 'Order Create')

@section('body')
<form action="{{ route('admin.orders.store') }}" method="POST" x-data="orderForm()">
    @csrf
    <div class="grid gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::singleselect
                name="order_user"
                label="{{ __('admin/orders.user_label') }}"
                helper="{{ __('admin/orders.user_helper') }}"
                :options="$users->map(fn($user) => [
                    'value' => $user->email,
                    'title' => $user->email,
                    'subtitle' => $user->fullname
                ])->toArray()"
                :selected="old('order_user')"
                required
            />
            <x-admin::select 
                name="order_currency"
                label="{{ __('admin/orders.currency_label') }}"
                helper="{{ __('admin/orders.currency_helper') }}"
                required
                x-model="selectedCurrency"
            >
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->code }}" {{ old('order_currency') === $currency->code ? 'selected' : '' }}>{{ $currency->code }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::singleselect
                name="order_coupon"
                label="{{ __('admin/orders.coupon_label') }}"
                helper="{{ __('admin/orders.coupon_helper') }}"
                :options="$coupons->map(fn($coupon) => [
                    'value' => $coupon->code,
                    'title' => $coupon->code,
                ])->toArray()"
                :selected="old('order_coupon')"
            />
            <x-admin::select 
                name="order_status"
                label="{{ __('admin/orders.status_label') }}"
                helper="{{ __('admin/orders.status_helper') }}"
                required
            >
                @foreach(['pending', 'processing', 'completed', 'cancelled', 'failed'] as $status)
                    <option value="{{ $status }}" {{ old('order_status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::toggle
                name="order_email"
                label="{{ __('admin/orders.email_label') }}"
                helper="{{ __('admin/orders.email_helper') }}"
                :checked="old('order_email')"
            />
        </div>
        <div>
            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.package_configuration_label') }}</h4>
            <span class="text-slate-500">{{ __('admin/orders.package_configuration_helper') }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::select 
                name="order_package"
                label="{{ __('admin/orders.package_label') }}"
                helper="{{ __('admin/orders.package_helper') }}"
                required
                x-model="selectedPackage"
                x-on:disabled="!selectedCurrency"
            >
                <template x-for="pkg in availablePackages" :key="pkg.id">
                    <option :value="pkg.id" :selected="pkg.id == selectedPackage" x-text="pkg.catalog_name + ' - ' + pkg.name"></option>
                </template>
            </x-admin::select>
            <x-admin::select 
                name="order_package_billing"
                label="{{ __('admin/orders.package_billing_label') }}"
                helper="{{ __('admin/orders.package_billing_helper') }}"
                required
                x-model="selectedPrice"
                x-on:disabled="!selectedPackage"
            >
                <template x-for="price in availablePrices" :key="price.id">
                    <option :value="price.id" :selected="price.id == selectedPrice" x-text="price.name"></option>
                </template>
            </x-admin::select>
        </div>
        <template x-if="currentVariants.length > 0 && hasAnyAvailableOptions">
            <div>
                <div class="mb-2">
                    <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.variant_option_label') }}</h4>
                    <span class="text-slate-500">{{ __('admin/orders.variant_option_helper') }}</span>
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
                                                    name="variant_options[{{ $variant['id'] }}]"
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
                                                    name="variant_options[{{ $variant['id'] }}]"
                                                    label="{{ $variant['name'] }}"
                                                    required
                                                >
                                                    @foreach ($variant['options'] as $option)
                                                        <x-admin::radio.option
                                                            name="variant_options[{{ $variant['id'] }}]"
                                                            label="{{ $option['name'] }}"
                                                            value="{{ $option['id'] }}"
                                                            x-show="getFilteredOptions({{ json_encode($variant) }}).some(o => o.id == {{ $option['id'] }})"
                                                            :checked="old('variant_options.' . $variant['id']) == $option['id']"
                                                        />
                                                    @endforeach
                                                </x-admin::radio.group>
                                                @break
                                            @case('checkbox')
                                                <x-admin::checkbox
                                                    name="variant_options[{{ $variant['id'] }}]"
                                                    label="{{ $variant['name'] }}"
                                                    :options="collect($variant['options'])->pluck('name', 'id')->toArray()"
                                                    :checked="old('variant_options.' . $variant['id'], [])"
                                                />
                                                @break
                                            @case('slider')
                                                <x-admin::slider
                                                    name="variant_options[{{ $variant['id'] }}]"
                                                    label="{{ $variant['name'] }}"
                                                    :options="collect($variant['options'])->map(fn($o) => ['value' => $o['id'], 'label' => $o['name']])->toArray()"
                                                    value="{{ old('variant_options.' . $variant['id'], $variant['options'][0]['id'] ?? null) }}"
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
        @if (!empty($checkoutSchema))
            @foreach ($checkoutSchema as $packageId => $schema)
                <template x-if="Number(selectedPackage) === {{ $packageId }}">
                    <div class="space-y-2">
                        <div>
                            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.additional_configuration_label') }}</h4>
                            <span class="text-slate-500">{{ __('admin/orders.additional_configuration_helper') }}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                            @foreach ($schema as $key => $field)
                                @if (in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                                    <x-admin::input
                                        name="configuration[{{$key}}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        type="{{ $field['type'] }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        :required="str_contains($field['rules'] ?? '', 'required')"
                                        :value="old('configuration.' . $key, $field['default'] ?? '')"
                                    />
                                @elseif ($field['type'] === 'textarea')
                                    <x-admin::textarea
                                        name="configuration[{{$key}}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        :required="str_contains($field['rules'] ?? '', 'required')"
                                    >{{ old('configuration.' . $key, $field['default'] ?? '') }}</x-admin::textarea>

                                @elseif ($field['type'] === 'toggle')
                                    <x-admin::toggle
                                        name="configuration[{{$key}}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        :checked="(bool) old('configuration.' . $key, $field['default'] ?? false)"
                                    />
                                @elseif ($field['type'] === 'select')
                                    <x-admin::select
                                        name="configuration[{{$key}}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        :required="str_contains($field['rules'] ?? '', 'required')"
                                    >
                                        @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                                            <option
                                                value="{{ $optValue }}"
                                                @selected(old('configuration.' . $key, $field['default'] ?? '') == $optValue)
                                            >{{ $optLabel }}</option>
                                        @endforeach
                                    </x-admin::select>
                                @elseif ($field['type'] === 'radio')
                                    <x-admin::radio.group
                                        name="configuration[{{$key}}]"
                                        label="{{ $field['label'] }}"
                                        helper="{{ $field['helper'] ?? '' }}"
                                        :required="str_contains($field['rules'] ?? '', 'required')"
                                    >
                                        @foreach ($field['options'] ?? [] as $optVal => $optLabel)
                                            <x-admin::radio.option
                                                name="configuration[{{$key}}]"
                                                value="{{ $optVal }}"
                                                label="{{ $optLabel }}"
                                                :checked="old('configuration.' . $key, $field['default'] ?? '') == $optVal"
                                            />
                                        @endforeach
                                    </x-admin::radio.group>
                                @elseif ($field['type'] === 'checkbox')
                                    <x-admin::checkbox
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
                </template>
            @endforeach
        @endif
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.orders') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
        </div>
    </div>
</form>
<script>
function orderForm() {
    return {
        selectedCurrency: '{{ old("order_currency") }}',
        selectedPackage: '{{ old("order_package") }}',
        selectedPrice: '{{ old("order_package_billing") }}',
        
        selections: @json(old('variant_options', [])),
        
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
                if (this.selectedCurrency) {
                    this.initializeFromOldValues();
                }
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

        initializeFromOldValues() {
            this.availablePackages = this.packages.filter(pkg => pkg.currencies[this.selectedCurrency] !== undefined);
            
            if (this.selectedPackage) {
                const pkg = this.packages.find(p => p.id == this.selectedPackage);
                if (pkg && pkg.currencies[this.selectedCurrency]) {
                    this.availablePrices = pkg.currencies[this.selectedCurrency].prices;
                    
                    if (this.selectedPrice) {
                        this.currentVariants = pkg.currencies[this.selectedCurrency].variants;
                        const price = this.availablePrices.find(p => p.id == this.selectedPrice);
                        this.selectedPriceName = price ? price.name : '';
                        
                        this.applyVariantDefaults();
                    }
                }
            }
        },
        
        onCurrencyChange() {
            if (!this.selectedCurrency) {
                this.availablePackages = [];
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
            this.selectedPrice = '';
            this.selectedPriceName = '';
            this.currentVariants = [];
            
            const pkg = this.packages.find(p => p.id == this.selectedPackage);
            if (pkg && pkg.currencies[this.selectedCurrency]) {
                this.availablePrices = pkg.currencies[this.selectedCurrency].prices;
            }
        },
        
        onPriceChange() {
            if (!this.selectedPrice) {
                this.selectedPriceName = '';
                this.currentVariants = [];
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
                    if (!Array.isArray(this.selections[variant.id])) {
                        let val = this.selections[variant.id];
                        if (val !== undefined && val !== null && val !== '') {
                            this.selections[variant.id] = Array.isArray(val) ? val : [parseInt(val)];
                        } else {
                            this.selections[variant.id] = [];
                        }
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
        },
    }
}
</script>
@endsection