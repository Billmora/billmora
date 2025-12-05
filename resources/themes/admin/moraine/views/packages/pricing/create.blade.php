@extends('admin::layouts.app')

@section('title', "Package Pricing - Create")

@section('body')
<div class="flex flex-col gap-5">
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    @if (session('error'))
        <x-admin::alert variant="danger" title="{{ session('error') }}" />
    @endif
    <form action="{{ route('admin.packages.pricing.store', ['id' => $package->id]) }}" method="POST" class="flex flex-col gap-5" x-data="{ pricingType: '{{ old('pricing_type', 'free') }}' }">
        @csrf
        <div class="flex flex-col gap-5">
            <div class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <div class="grid grid-cols-none md:grid-cols-2 gap-5">
                    <x-admin::input 
                        name="pricing_name"
                        type="text"
                        label="{{ __('admin/packages.pricing.name_label') }}"
                        helper="{{ __('admin/packages.pricing.name_helper') }}"
                        value="{{ old('pricing_name') }}"
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
                            value="{{ old('pricing_time_interval') }}"
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
                            <option value="hourly"  {{ old('pricing_billing_period') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                            <option value="daily"   {{ old('pricing_billing_period') === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly"  {{ old('pricing_billing_period') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('pricing_billing_period') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly"  {{ old('pricing_billing_period') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </x-admin::select>
                    </div>
                </div>
            </div>
            <div class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl" x-show="pricingType !== 'free'">
                <div class="flex flex-col gap-6">
                    @foreach ($currencies as $currency)
                        <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-4 gap-5 border-2 border-billmora-2 p-4 rounded-xl">
                            <x-admin::input 
                                name="rates[{{ $currency->code }}][currency]"
                                type="text"
                                label="{{ __('admin/packages.pricing.currency_code_label') }}"
                                helper="{{ __('admin/packages.pricing.currency_code_helper') }}"
                                value="{{ $currency->code }}"
                                readonly
                                required
                            />
                            <x-admin::input 
                                name="rates[{{ $currency->code }}][price]"
                                type="number"
                                step="0.01"
                                min="1"
                                label="{{ __('admin/packages.pricing.price_label') }}"
                                helper="{{ __('admin/packages.pricing.price_helper') }}"
                                value="{{ old('rates.' . $currency->code . '.price') }}"
                                required
                            />
                            <x-admin::input 
                                name="rates[{{ $currency->code }}][setup_fee]"
                                type="number"
                                step="0.01"
                                min="0"
                                label="{{ __('admin/packages.pricing.setup_fee_label') }}"
                                helper="{{ __('admin/packages.pricing.setup_fee_helper') }}"
                                value="{{ old('rates.' . $currency->code . '.setup_fee', 0) }}"
                                required
                            />
                            @if (!$currency->is_default)
                                <x-admin::toggle 
                                    name="rates[{{ $currency->code }}][enabled]"
                                    label="{{ __('admin/packages.pricing.enabled_label') }}"
                                    helper="{{ __('admin/packages.pricing.enabled_helper') }}"
                                    :checked="old('rates.' . $currency->code . '.enabled', false)"
                                />
                            @else
                                <input type="hidden" name="rates[{{ $currency->code }}][enabled]" value="1" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.packages.pricing', ['id' => $package->id]) }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
        </div>
    </form>
</div>
@endsection