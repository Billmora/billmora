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
                <option value="pending" {{ old('order_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ old('order_status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="completed" {{ old('order_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('order_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="failed" {{ old('order_status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </x-admin::select>
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
        <template x-if="currentVariants.length > 0 && hasAnyAvailableOptions()">
            <div>
                <div class="mb-2">
                    <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.variant_option_label') }}</h4>
                    <span class="text-slate-500">{{ __('admin/orders.variant_option_helper') }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    <template x-for="variant in currentVariants" :key="variant.id">
                        <div x-show="getFilteredOptions(variant).length > 0">
                            <label class="block text-sm font-semibold text-slate-600 mb-2" x-text="variant.name"></label>
                            <template x-if="variant.type === 'select'">
                                <select 
                                    :name="'variant_options[' + variant.id + ']'"
                                    class="w-full px-3 py-2 border-2 border-billmora-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-billmora-primary cursor-pointer"
                                    x-init="$el.value = getFilteredOptions(variant)[0]?.id || ''"
                                >
                                    <template x-for="option in getFilteredOptions(variant)" :key="option.id">
                                        <option :value="option.id" x-text="option.name"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="variant.type === 'radio'">
                                <div class="space-y-2">
                                    <template x-for="(option, index) in getFilteredOptions(variant)" :key="option.id">
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input 
                                                type="radio" 
                                                :name="'variant_options[' + variant.id + ']'"
                                                :value="option.id"
                                                :checked="index === 0"
                                                class="accent-billmora-primary"
                                                required
                                            />
                                            <span x-text="option.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </template>
                            <template x-if="variant.type === 'slider'">
                                <div x-data="{ sliderIdx: 0 }" class="mt-4">
                                    <div class="relative mb-10">
                                        <input 
                                            type="range" 
                                            min="0" 
                                            :max="Math.max(0, getFilteredOptions(variant).length - 1)" 
                                            step="1" 
                                            class="w-full h-2 cursor-pointer accent-billmora-primary"
                                            x-model="sliderIdx"
                                        />
                                        <template x-for="(option, i) in getFilteredOptions(variant)" :key="option.id">
                                            <span 
                                                class="text-sm text-slate-700 absolute -bottom-6"
                                                :class="{
                                                    'start-0 text-start': i === 0,
                                                    'end-0 text-end': i === getFilteredOptions(variant).length - 1,
                                                    'start-1/2 -translate-x-1/2': i > 0 && i < getFilteredOptions(variant).length - 1
                                                }"
                                                x-show="i === 0 || i === getFilteredOptions(variant).length - 1 || getFilteredOptions(variant).length <= 3"
                                            >
                                                <span x-text="option.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                    <input 
                                        type="hidden" 
                                        :name="'variant_options[' + variant.id + ']'" 
                                        :value="getFilteredOptions(variant)[sliderIdx]?.id || ''"
                                    />
                                </div>
                            </template>
                            <template x-if="variant.type === 'checkbox'">
                                <div class="space-y-2">
                                    <template x-for="option in getFilteredOptions(variant)" :key="option.id">
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input 
                                                type="checkbox" 
                                                :name="'variant_options[' + variant.id + '][]'"
                                                :value="option.id"
                                                class="accent-billmora-primary"
                                            />
                                            <span x-text="option.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.orders') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
        </div>
    </div>
</form>
<script>
/**
 * Initialize Alpine.js component for order form with dynamic package and variant handling.
 *
 * @return object
 */
function orderForm() {
    return {
        selectedCurrency: '{{ old("order_currency") }}',
        selectedPackage: '{{ old("order_package") }}',
        selectedPrice: '{{ old("order_package_billing") }}',
        selectedPriceName: '',
        
        availablePackages: [],
        availablePrices: [],
        currentVariants: [],
        
        packages: @json($packagesPayload),
        
        /**
         * Initialize watchers and load old form values if present.
         *
         * @return void
         */
        init() {
            this.$watch('selectedCurrency', (newValue, oldValue) => {
                if (oldValue === undefined) return;
                
                if (newValue && newValue !== oldValue) {
                    this.onCurrencyChange();
                }
            });
            
            this.$watch('selectedPackage', (newValue, oldValue) => {
                if (oldValue === undefined) return;
                
                if (newValue && newValue !== oldValue) {
                    this.onPackageChange();
                }
            });
            
            this.$watch('selectedPrice', (newValue, oldValue) => {
                if (oldValue === undefined) return;
                
                if (newValue && newValue !== oldValue) {
                    this.onPriceChange();
                }
            });
            
            this.$nextTick(() => {
                if (this.selectedCurrency) {
                    this.initializeFromOldValues();
                }
            });
        },
        
        /**
         * Restore form state from old input values after validation errors.
         *
         * @return void
         */
        initializeFromOldValues() {
            this.availablePackages = this.packages.filter(pkg => {
                return pkg.currencies[this.selectedCurrency] !== undefined;
            });
            
            if (this.selectedPackage) {
                const pkg = this.packages.find(p => p.id == this.selectedPackage);
                if (pkg && pkg.currencies[this.selectedCurrency]) {
                    this.availablePrices = pkg.currencies[this.selectedCurrency].prices;
                    
                    if (this.selectedPrice) {
                        this.currentVariants = pkg.currencies[this.selectedCurrency].variants;
                        
                        const price = this.availablePrices.find(p => p.id == this.selectedPrice);
                        this.selectedPriceName = price ? price.name : '';
                    }
                }
            }
        },
        
        /**
         * Handle currency change and reset dependent selections.
         *
         * @return void
         */
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
            
            this.availablePackages = this.packages.filter(pkg => {
                return pkg.currencies[this.selectedCurrency] !== undefined;
            });  
        },
        
        /**
         * Handle package change and load available prices.
         *
         * @return void
         */
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
        
        /**
         * Handle billing cycle change and load variants with matching prices.
         *
         * @return void
         */
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
            }
        },
        
        /**
         * Check if any variant has options available for selected billing cycle.
         *
         * @return boolean
         */
        hasAnyAvailableOptions() {
            if (!this.selectedPriceName) return false;
            
            return this.currentVariants.some(variant => {
                return this.getFilteredOptions(variant).length > 0;
            });
        },
        
        /**
         * Get variant options that have prices for selected billing cycle.
         *
         * @param object variant
         * @return array
         */
        getFilteredOptions(variant) {
            if (!this.selectedPriceName) {
                return [];
            }
            
            return (variant.options || []).filter(option => {
                return option.prices_by_name && option.prices_by_name[this.selectedPriceName] !== undefined;
            });
        },
    }
}
</script>
@endsection