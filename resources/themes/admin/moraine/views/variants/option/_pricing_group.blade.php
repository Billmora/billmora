@props([
    'index',
    'pricing' => [],
    'currencies',
    'canDelete' => false,
])

@php
    $type = $pricing['type'] ?? 'free';
    $billing = $pricing['billing_period'] ?? null;
@endphp

<div class="price-group w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl mb-5"
     x-data="{ type: '{{ $type }}' }"
>
    @if ($canDelete)
        <div class="flex justify-end mb-2">
            <button type="button"
                    x-on:click="$el.closest('.price-group').remove()"
                    class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-2 py-1 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.delete') }}
            </button>
        </div>
    @endif

    <div class="grid grid-cols-none md:grid-cols-2 gap-5">
        <x-admin::input
            name="pricings[{{ $index }}][name]"
            type="text"
            label="{{ __('admin/variants.options.pricing.name_label') }}"
            helper="{{ __('admin/variants.options.pricing.name_helper') }}"
            value="{{ $pricing['name'] ?? '' }}"
            required
        />

        <x-admin::select
            name="pricings[{{ $index }}][type]"
            label="{{ __('admin/variants.options.pricing.type_label') }}"
            helper="{{ __('admin/variants.options.pricing.type_helper') }}"
            x-model="type"
            required
        >
            <option value="free" {{ $type === 'free' ? 'selected' : '' }}>Free</option>
            <option value="onetime" {{ $type === 'onetime' ? 'selected' : '' }}>One-Time</option>
            <option value="recurring" {{ $type === 'recurring' ? 'selected' : '' }}>Recurring</option>
        </x-admin::select>

        <div x-show="type === 'recurring'" style="display:none;">
            <x-admin::input
                name="pricings[{{ $index }}][time_interval]"
                type="number"
                min="1"
                label="{{ __('admin/variants.options.pricing.time_interval_label') }}"
                helper="{{ __('admin/variants.options.pricing.time_interval_helper') }}"
                value="{{ $pricing['time_interval'] ?? null }}"
                required
            />
        </div>

        <div x-show="type === 'recurring'" style="display:none;">
            <x-admin::select
                name="pricings[{{ $index }}][billing_period]"
                label="{{ __('admin/variants.options.pricing.billing_period_label') }}"
                helper="{{ __('admin/variants.options.pricing.billing_period_helper') }}"
                required
            >
                <option value="hourly"  {{ $billing === 'hourly' ? 'selected' : '' }}>Hourly</option>
                <option value="daily"   {{ $billing === 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly"  {{ $billing === 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ $billing === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="yearly"  {{ $billing === 'yearly' ? 'selected' : '' }}>Yearly</option>
            </x-admin::select>
        </div>
    </div>

    <div class="mt-6" x-show="type !== 'free'" style="display:none;">
        <div class="flex flex-col gap-6">
            @foreach ($currencies as $currency)
                @php($rate = $pricing['rates'][$currency->code] ?? [])
                <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-4 gap-5 border-2 border-billmora-2 p-4 rounded-xl">
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
                        type="number"
                        step="0.01"
                        min="1"
                        label="{{ __('admin/variants.options.pricing.price_label') }}"
                        helper="{{ __('admin/variants.options.pricing.price_helper') }}"
                        value="{{ $rate['price'] ?? null }}"
                        required
                    />

                    <x-admin::input
                        name="pricings[{{ $index }}][rates][{{ $currency->code }}][setup_fee]"
                        type="number"
                        step="0.01"
                        min="0"
                        label="{{ __('admin/variants.options.pricing.setup_fee_label') }}"
                        helper="{{ __('admin/variants.options.pricing.setup_fee_helper') }}"
                        value="{{ $rate['setup_fee'] ?? 0 }}"
                        required
                    />

                    @if (!$currency->is_default)
                        <x-admin::toggle
                            name="pricings[{{ $index }}][rates][{{ $currency->code }}][enabled]"
                            label="{{ __('admin/variants.options.pricing.enabled_label') }}"
                            helper="{{ __('admin/variants.options.pricing.enabled_helper') }}"
                            :checked="(bool)($rate['enabled'] ?? false)"
                        />
                    @else
                        <input type="hidden"
                               name="pricings[{{ $index }}][rates][{{ $currency->code }}][enabled]"
                               value="1" />
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
