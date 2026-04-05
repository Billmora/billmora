<form
    action="{{ route('admin.variants.options.update', ['variant' => $variant->id, 'option' => $option->id]) }}"
    method="POST"
    class="flex flex-col gap-5"
>
    @csrf
    @method('PUT')
    <div class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl space-y-4">
        <div class="grid grid-cols-none md:grid-cols-2 gap-5">
            <x-admin::input
                name="variant_options_name"
                type="text"
                label="{{ __('admin/variants.options.name_label') }}"
                helper="{{ __('admin/variants.options.name_helper') }}"
                value="{{ old('variant_options_name', $option->name ?? '') }}"
                required
            />
            <x-admin::input
                name="variant_options_value"
                type="text"
                label="{{ __('admin/variants.options.value_label') }}"
                helper="{{ __('admin/variants.options.value_helper') }}"
                value="{{ old('variant_options_value', $option->value ?? '') }}"
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
                wire:key="pricing-{{ $pricing['uid'] }}"
                class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl mb-5"
            >
                <input type="hidden" name="pricings[{{ $index }}][uid]" value="{{ $pricing['uid'] }}" />
                @if (!empty($pricing['id']))
                    <input type="hidden" name="pricings[{{ $index }}][id]" value="{{ $pricing['id'] }}" />
                @endif
                <div class="flex justify-end mb-2 {{ count($pricings) <= 1 ? 'hidden' : '' }}">
                    <button
                        type="button"
                        wire:click="removePrice('{{ $pricing['uid'] }}')"
                        class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-2 py-1 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                    >
                        {{ __('common.delete') }}
                    </button>
                </div>
                <div
                    x-data="{ type: '{{ $pricing['type'] ?? 'free' }}' }"
                    x-on:change="if ($event.target.name === 'pricings[{{ $index }}][type]') type = $event.target.value"
                >
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
                            label="{{ __('admin/variants.options.pricing.type_label') }}"
                            helper="{{ __('admin/variants.options.pricing.type_helper') }}"
                            required
                        >
                            @foreach (['free', 'onetime', 'recurring'] as $type)
                                <option value="{{ $type }}" {{ old('pricings.'.$index.'.type', $pricing['type'] ?? 'free') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </x-admin::select>
                        <div
                            x-show="type === 'recurring'"
                            style="{{ ($pricing['type'] ?? 'free') !== 'recurring' ? 'display: none;' : '' }}"
                        >
                            <x-admin::input
                                name="pricings[{{ $index }}][time_interval]"
                                wire:model="pricings.{{ $index }}.time_interval"
                                value="{{ $pricing['time_interval'] ?? '' }}"
                                type="number"
                                min="1"
                                label="{{ __('admin/variants.options.pricing.time_interval_label') }}"
                                helper="{{ __('admin/variants.options.pricing.time_interval_helper') }}"
                                required
                            />
                        </div>
                        <div
                            x-show="type === 'recurring'"
                            style="{{ ($pricing['type'] ?? 'free') !== 'recurring' ? 'display: none;' : '' }}"
                        >
                            <x-admin::select
                                name="pricings[{{ $index }}][billing_period]"
                                wire:model="pricings.{{ $index }}.billing_period"
                                label="{{ __('admin/variants.options.pricing.billing_period_label') }}"
                                helper="{{ __('admin/variants.options.pricing.billing_period_helper') }}"
                                required
                            >
                                <option value="daily" @selected(($pricing['billing_period'] ?? '') === 'daily')>Daily</option>
                                <option value="weekly" @selected(($pricing['billing_period'] ?? '') === 'weekly')>Weekly</option>
                                <option value="monthly" @selected(($pricing['billing_period'] ?? '') === 'monthly')>Monthly</option>
                                <option value="yearly" @selected(($pricing['billing_period'] ?? '') === 'yearly')>Yearly</option>
                            </x-admin::select>
                        </div>
                    </div>
                    <div
                        class="mt-6"
                        x-show="type !== 'free'"
                        style="{{ ($pricing['type'] ?? 'free') === 'free' ? 'display: none;' : '' }}"
                    >
                        <div class="flex flex-col gap-6">
                            @foreach ($currencies as $currency)
                                <div
                                    wire:key="rate-{{ $pricing['uid'] }}-{{ $currency->code }}"
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
            {{ __('common.update') }}
        </button>
    </div>
</form>