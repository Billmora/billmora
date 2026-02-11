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
        <template x-if="currentVariants.length > 0 && hasAnyAvailableOptions()">
            <div>
                <div class="mb-2">
                    <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.variant_option_label') }}</h4>
                    <span class="text-slate-500">{{ __('admin/orders.variant_option_helper') }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    <template x-for="variant in currentVariants" :key="variant.id">
                        <template x-if="getFilteredOptions(variant).length > 0">
                            <div class="col-span-1">
                                <label class="block text-sm font-semibold text-slate-600 mb-2" x-text="variant.name"></label>
                                <template x-if="variant.type === 'select'">
                                    <select 
                                        :name="'variant_options[' + variant.id + ']'"
                                        class="w-full px-3 py-2 border-2 border-billmora-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-billmora-primary cursor-pointer"
                                        x-model="selections[variant.id]"
                                    >
                                        <option value="" disabled selected>Select Option</option>
                                        <template x-for="option in getFilteredOptions(variant)" :key="option.id">
                                            <option :value="option.id" x-text="option.name" :selected="selections[variant.id] == option.id"></option>
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
                                                    x-model="selections[variant.id]"
                                                    class="accent-billmora-primary"
                                                />
                                                <span x-text="option.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="variant.type === 'slider'">
                                    <div x-data="{ 
                                        sliderIdx: 0,
                                        init() {
                                            let opts = this.getFilteredOptions(variant);
                                            let selectedId = this.selections[variant.id];
                                            let foundIndex = opts.findIndex(o => o.id == selectedId);
                                            this.sliderIdx = foundIndex !== -1 ? foundIndex : 0;
                                        }
                                    }" class="mt-4">
                                        <div class="relative mb-10">
                                            <input 
                                                type="range" 
                                                min="0" 
                                                :max="Math.max(0, getFilteredOptions(variant).length - 1)" 
                                                step="1" 
                                                class="w-full h-2 cursor-pointer accent-billmora-primary"
                                                x-model="sliderIdx"
                                                @input="selections[variant.id] = getFilteredOptions(variant)[sliderIdx]?.id"
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
                                                    x-model="selections[variant.id]"
                                                    class="accent-billmora-primary"
                                                />
                                                <span x-text="option.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
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
            
            this.availablePackages = this.packages.filter(pkg => {
                return pkg.currencies[this.selectedCurrency] !== undefined;
            });  
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
            }
        },
        
        hasAnyAvailableOptions() {
            if (!this.selectedPriceName) return false;
            
            return this.currentVariants.some(variant => {
                return this.getFilteredOptions(variant).length > 0;
            });
        },
        
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