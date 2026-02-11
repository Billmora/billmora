@extends('admin::layouts.app')

@section('title', "Service Edit - {$service->name}")

@section('body')
<div class="flex flex-col lg:flex-row gap-5">
    <form action="{{ route('admin.services.update', ['service' => $service->id]) }}" method="POST" x-data="serviceForm()" class="w-full lg:w-5/7 flex flex-col gap-5">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <div class="w-full">
                <label for="user_id" class="flex text-slate-600 font-semibold mb-1">User</label>
                <a href="{{ route('admin.users.summary', ['id' => $service->user_id]) }}" class="relative inline-block max-w-150 w-full group" target="_blank">
                    <input type="text" name="user_id" id="user_id" value="{{ $service->user->email }}" class="w-full px-3 py-2.25 bg-billmora-1 placeholder:text-gray-400 border-2 border-billmora-2 rounded-xl cursor-not-allowed" disabled>
                    <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                        <button type="button" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">
                            Go to User
                        </button>
                    </div>
                </a>
            </div>
            <x-admin::input 
                name="service_name"
                label="Name"
                value="{{ $service->name }}"
                required
                disabled
            />
            <x-admin::select 
                name="service_currency"
                label="Currency"
                required
                x-model="selectedCurrency"
            >
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->code }}">{{ $currency->code }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::select
                name="service_status"
                label="{{ __('admin/orders.status_label') }}"
                required
            >
                @foreach(['pending', 'active', 'suspended', 'terminated', 'cancelled'] as $status)
                    <option value="{{ $status }}" {{ old('service_status', $service->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::toggle 
                name="service_recalculate_price"
                label="Recalculate on Save?"
            />
            <x-admin::input 
                name="service_next_due_date" 
                label="Next Due Date" 
                type="date"
                value="{{ old('service_next_due_date', $service->next_due_date?->format('Y-m-d')) }}"
            />
            <x-admin::input 
                name="service_price" 
                label="Price" 
                type="number" 
                step="0.01"
                value="{{ old('service_price', $service->price) }}"
            />
            <x-admin::input 
                name="service_setup_fee" 
                label="Setup Fee" 
                type="number" 
                step="0.01"
                value="{{ old('service_setup_fee', $service->setup_fee) }}"
            />
        </div>
        <div>
            <h4 class="text-lg font-semibold text-slate-600">Package Configuration</h4>
            <span class="text-slate-500">Define the base package and billing period for this service.</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::select 
                name="package_id"
                label="Package"
                required
                x-model="selectedPackage"
                x-on:disabled="!selectedCurrency"
            >
                <template x-for="pkg in availablePackages" :key="pkg.id">
                    <option :value="pkg.id" x-text="pkg.catalog_name + ' - ' + pkg.name"></option>
                </template>
            </x-admin::select>

            <x-admin::select 
                name="package_price_id"
                label="Price"
                required
                x-model="selectedPrice"
                x-on:disabled="!selectedPackage"
            >
                <template x-for="price in availablePrices" :key="price.id">
                    <option :value="price.id" x-text="price.name"></option>
                </template>
            </x-admin::select>
        </div>
        <template x-if="currentVariants.length > 0 && hasAnyAvailableOptions()">
            <div>
                <div class="mb-2">
                    <h4 class="text-lg font-semibold text-slate-600">Variant Options</h4>
                    <span class="text-slate-500">Select additional variants are available for the chosen package.</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    <template x-for="variant in currentVariants" :key="variant.id">
                        <template x-if="getFilteredOptions(variant).length > 0">
                            <div class="col-span-1">
                                <label class="block text-sm font-semibold text-slate-600 mb-2" x-text="variant.name"></label>
                                <template x-if="variant.type === 'select'">
                                    <select 
                                        :name="'variant_selections[' + variant.id + ']'"
                                        class="w-full px-3 py-2 border-2 border-billmora-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-billmora-primary cursor-pointer"
                                        x-model="selections[variant.id]"
                                    >
                                        <template x-for="option in getFilteredOptions(variant)" :key="option.id">
                                            <option 
                                                :value="option.id" 
                                                x-text="option.name"
                                                :selected="selections[variant.id] == option.id"
                                            ></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="variant.type === 'radio'">
                                    <div class="space-y-2">
                                        <template x-for="option in getFilteredOptions(variant)" :key="option.id">
                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                <input 
                                                    type="radio" 
                                                    :name="'variant_selections[' + variant.id + ']'"
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
                                                x-on:input="selections[variant.id] = getFilteredOptions(variant)[sliderIdx]?.id"
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
                                            :name="'variant_selections[' + variant.id + ']'" 
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
                                                    :name="'variant_selections[' + variant.id + '][]'"
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
        serviceName: '{{ $service->name }}',
        
        selections: @json(old('variant_selections', $service->variant_selections ?? [])),
        
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
                    
                    const priceObj = this.availablePrices.find(p => p.id == this.selectedPrice);
                    if (priceObj) {
                        this.selectedPriceName = priceObj.name;
                    }
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
                this.serviceName = '';
                return;
            }
            
            const pkg = this.packages.find(p => p.id == this.selectedPackage);
            if (pkg) {
                this.serviceName = pkg.name;

                if (pkg.currencies[this.selectedCurrency]) {
                    this.availablePrices = pkg.currencies[this.selectedCurrency].prices;
                    this.currentVariants = pkg.currencies[this.selectedCurrency].variants;
                    
                    if (this.availablePrices.length > 0) {
                        if (!this.selectedPrice) {
                            this.selectedPrice = this.availablePrices[0].id;
                            this.selectedPriceName = this.availablePrices[0].name;
                        } else {
                            const price = this.availablePrices.find(p => p.id == this.selectedPrice);
                            this.selectedPriceName = price ? price.name : this.availablePrices[0].name;
                        }
                    }
                }
            }
        },

        onPriceChange() {
            if (!this.selectedPrice) {
                this.selectedPriceName = '';
                return;
            }
            const price = this.availablePrices.find(p => p.id == this.selectedPrice);
            this.selectedPriceName = price ? price.name : '';
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