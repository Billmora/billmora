<form 
    action="{{ route('admin.variants.options.store', ['variant' => $variant->id]) }}" 
    method="POST" 
    class="flex flex-col gap-5"
>
    @csrf
    <div class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl space-y-4">
        <div class="grid grid-cols-none md:grid-cols-2 gap-5">
            <x-admin::input
                name="variant_options_name"
                type="text"
                label="{{ __('admin/variants.options.name_label') }}"
                helper="{{ __('admin/variants.options.name_helper') }}"
                value="{{ old('variant_options_name') }}"
                required
            />
            <x-admin::input
                name="variant_options_value"
                type="text"
                label="{{ __('admin/variants.options.value_label') }}"
                helper="{{ __('admin/variants.options.value_helper') }}"
                value="{{ old('variant_options_value') }}"
                required
            />
        </div>
    </div>
    <div class="flex flex-col sm:flex-row justify-between gap-4 sm:items-center">
        <div>
            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/variants.options.pricing_label') }}</h4>
            <span class="text-slate-500">{{ __('admin/variants.options.pricing_helper') }}</span>
        </div>
        <button
            type="button"
            wire:click="addPrice"
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer"
        >
            {{ __('admin/variants.options.add_new_price_label') }}
        </button>
    </div>
    <div class="flex flex-col gap-4">
        @foreach($pricings as $index => $pricing)
            <div 
                wire:key="pricing-group-{{ $index }}" 
                class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl mb-5"
                x-data="{ type: '{{ $pricing['type'] ?? 'free' }}' }"
            >
                @if ($index > 0)
                    <div class="flex justify-end mb-2">
                        <button 
                            type="button"
                            wire:click="removePrice({{ $index }})"
                            class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-2 py-1 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                        >
                            {{ __('common.delete') }}
                        </button>
                    </div>
                @endif
                <div class="grid grid-cols-none md:grid-cols-2 gap-5">
                    <x-admin::input
                        name="pricings[{{ $index }}][name]"
                        wire:model="pricings.{{ $index }}.name"
                        value="{{ $pricing['name'] ?? '' }}"
                        type="text"
                        label="{{ __('admin/variants.options.pricing.name_label') }}"
                        helper="{{ __('admin/variants.options.pricing.name_helper') }}"
                        required
                    />
                    <x-admin::select
                        name="pricings[{{ $index }}][type]"
                        wire:model="pricings.{{ $index }}.type"
                        x-model="type"
                        label="{{ __('admin/variants.options.pricing.type_label') }}"
                        helper="{{ __('admin/variants.options.pricing.type_helper') }}"
                        required
                    >
                        <option value="free" @selected(($pricing['type'] ?? '') === 'free')>Free</option>
                        <option value="onetime" @selected(($pricing['type'] ?? '') === 'onetime')>One-Time</option>
                        <option value="recurring" @selected(($pricing['type'] ?? '') === 'recurring')>Recurring</option>
                    </x-admin::select>
                    <div 
                        x-show="type === 'recurring'" 
                        style="{{ ($pricing['type'] ?? '') === 'recurring' ? '' : 'display: none;' }}"
                    >
                        <x-admin::input
                            name="pricings[{{ $index }}][time_interval]"
                            wire:model="pricings.{{ $index }}.time_interval"
                            value="{{ $pricing['time_interval'] ?? '' }}"
                            type="number"
                            min="1"
                            label="{{ __('admin/variants.options.pricing.time_interval_label') }}"
                            helper="{{ __('admin/variants.options.pricing.time_interval_helper') }}"
                            x-bind:required="type === 'recurring'"
                        />
                    </div>
                    <div 
                        x-show="type === 'recurring'" 
                        style="{{ ($pricing['type'] ?? '') === 'recurring' ? '' : 'display: none;' }}"
                    >
                        <x-admin::select
                            name="pricings[{{ $index }}][billing_period]"
                            wire:model="pricings.{{ $index }}.billing_period"
                            label="{{ __('admin/variants.options.pricing.billing_period_label') }}"
                            helper="{{ __('admin/variants.options.pricing.billing_period_helper') }}"
                            x-bind:required="type === 'recurring'"
                        >
                            @foreach (['daily', 'weekly', 'monthly', 'yearly'] as $period)
                                <option value="{{ $period }}" {{ old('pricings.'.$index.'.billing_period') == $period ? 'selected' : '' }}>{{ ucfirst($period) }}</option>
                            @endforeach
                        </x-admin::select>
                    </div>
                </div>
                <div 
                    class="mt-6" 
                    x-show="type !== 'free'" 
                    style="{{ ($pricing['type'] ?? '') !== 'free' ? '' : 'display: none;' }}"
                >
                    <div class="flex flex-col gap-6">
                        @foreach ($currencies as $currency)
                            <div 
                                wire:key="rate-{{ $index }}-{{ $currency->code }}" 
                                class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-4 gap-5 border-2 border-billmora-2 p-4 rounded-xl"
                            >
                                <x-admin::input
                                    name="pricings[{{ $index }}][rates][{{ $currency->code }}][currency]"
                                    type="text"
                                    label="{{ __('admin/variants.options.pricing.currency_code_label') }}"
                                    helper="{{ __('admin/variants.options.pricing.currency_code_helper') }}"
                                    value="{{ $currency->code }}"
                                    readonly
                                    required
                                />
                                <x-admin::input
                                    name="pricings[{{ $index }}][rates][{{ $currency->code }}][price]"
                                    wire:model="pricings.{{ $index }}.rates.{{ $currency->code }}.price"
                                    value="{{ $pricing['rates'][$currency->code]['price'] ?? '' }}"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    label="{{ __('admin/variants.options.pricing.price_label') }}"
                                    helper="{{ __('admin/variants.options.pricing.price_helper') }}"
                                    x-bind:required="type !== 'free'"
                                />

                                <x-admin::input
                                    name="pricings[{{ $index }}][rates][{{ $currency->code }}][setup_fee]"
                                    wire:model="pricings.{{ $index }}.rates.{{ $currency->code }}.setup_fee"
                                    value="{{ $pricing['rates'][$currency->code]['setup_fee'] ?? '0' }}"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    label="{{ __('admin/variants.options.pricing.setup_fee_label') }}"
                                    helper="{{ __('admin/variants.options.pricing.setup_fee_helper') }}"
                                    x-bind:required="type !== 'free'"
                                />

                                @if (!$currency->is_default)
                                    <x-admin::toggle
                                        name="pricings[{{ $index }}][rates][{{ $currency->code }}][enabled]"
                                        wire:model="pricings.{{ $index }}.rates.{{ $currency->code }}.enabled"
                                        label="{{ __('admin/variants.options.pricing.enabled_label') }}"
                                        helper="{{ __('admin/variants.options.pricing.enabled_helper') }}"
                                        :checked="(bool) ($pricing['rates'][$currency->code]['enabled'] ?? false)"
                                    />
                                @else
                                    <input 
                                        type="hidden"
                                        name="pricings[{{ $index }}][rates][{{ $currency->code }}][enabled]"
                                        value="1" 
                                    />
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="flex gap-4 ml-auto">
        <a 
            href="{{ route('admin.variants.options', ['variant' => $variant->id]) }}" 
            class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors duration-150"
        >
            {{ __('common.cancel') }}
        </a>
        <button 
            type="submit" 
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors duration-150 cursor-pointer"
        >
            {{ __('common.create') }}
        </button>
    </div>
</form>