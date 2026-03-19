@extends('admin::layouts.app')

@section('title', "Package Pricing - Edit")

@section('body')
<form action="{{ route('admin.packages.pricing.update', ['package' => $package->id, 'pricing' => $pricing->id]) }}" method="POST" class="flex flex-col gap-5" x-data="{ pricingType: '{{ old('pricing_type', $pricing->type) }}' }">
    @csrf
    @method('PUT')
    <div class="flex flex-col gap-5">
        <div class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <div class="grid grid-cols-none md:grid-cols-2 gap-5">
                <x-admin::input 
                    name="pricing_name"
                    type="text"
                    label="{{ __('admin/packages.pricing.name_label') }}"
                    helper="{{ __('admin/packages.pricing.name_helper') }}"
                    value="{{ old('pricing_name', $pricing->name) }}"
                    required 
                />
                <x-admin::select 
                    name="pricing_type"
                    label="{{ __('admin/packages.pricing.type_label') }}"
                    helper="{{ __('admin/packages.pricing.type_helper') }}"
                    x-model="pricingType"
                    required
                >
                    <option value="free">Free</option>
                    <option value="onetime">One-Time</option>
                    <option value="recurring">Recurring</option>
                </x-admin::select>
                <div x-show="pricingType === 'recurring'">
                    <x-admin::input 
                        name="pricing_time_interval"
                        type="number"
                        min="1"
                        label="{{ __('admin/packages.pricing.time_interval_label') }}"
                        helper="{{ __('admin/packages.pricing.time_interval_helper') }}"
                        value="{{ old('pricing_time_interval', $pricing->time_interval) }}"
                        required
                    />
                </div>
                <div x-show="pricingType === 'recurring'">
                    <x-admin::select 
                        name="pricing_billing_period"
                        label="{{ __('admin/packages.pricing.billing_period_label') }}"
                        helper="{{ __('admin/packages.pricing.billing_period_helper') }}"
                        required
                    >
                        <option value="daily"   {{ old('pricing_billing_period', $pricing->billing_period) === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly"  {{ old('pricing_billing_period', $pricing->billing_period) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('pricing_billing_period', $pricing->billing_period) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly"  {{ old('pricing_billing_period', $pricing->billing_period) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </x-admin::select>
                </div>
            </div>
        </div>
        <div class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl" x-show="pricingType !== 'free'">
            <div class="flex flex-col gap-6">
                @foreach ($currencies as $code => $currency)
                    @php
                        $rate = $pricing->rates[$code];
                        $oldPrice = old('rates.' . $code . '.price', $rate['price'] ?? 0);
                        $oldSetupFee = old('rates.' . $code . '.setup_fee', $rate['setup_fee'] ?? 0);
                        $oldEnabled = (bool) old('rates.' . $code . '.enabled', $rate['enabled'] ?? false);
                    @endphp
                    <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-4 gap-5 border-2 border-billmora-2 p-4 rounded-xl">
                        <x-admin::input
                            name="rates[{{ $code }}][currency]"
                            type="text"
                            label="{{ __('admin/packages.pricing.currency_code_label') }}"
                            helper="{{ __('admin/packages.pricing.currency_code_helper') }}"
                            value="{{ $code }}"
                            readonly
                            required
                        />
                        <x-admin::input
                            name="rates[{{ $code }}][price]"
                            type="number"
                            step="0.01"
                            min="1"
                            label="{{ __('admin/packages.pricing.price_label') }}"
                            helper="{{ __('admin/packages.pricing.price_helper') }}"
                            :value="$oldPrice"
                            required
                        />
                        <x-admin::input
                            name="rates[{{ $code }}][setup_fee]"
                            type="number"
                            step="0.01"
                            min="0"
                            label="{{ __('admin/packages.pricing.setup_fee_label') }}"
                            helper="{{ __('admin/packages.pricing.setup_fee_helper') }}"
                            :value="$oldSetupFee"
                            required
                        />
                        @if (!$currency->is_default)
                            <x-admin::toggle
                                name="rates[{{ $code }}][enabled]"
                                label="{{ __('admin/packages.pricing.enabled_label') }}"
                                helper="{{ __('admin/packages.pricing.enabled_helper') }}"
                                :checked="$oldEnabled"
                            />
                        @else
                            <input type="hidden" name="rates[{{ $code }}][enabled]" value="1" />
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.packages.pricing', ['package' => $package->id]) }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.save') }}</button>
    </div>
</form>
@endsection