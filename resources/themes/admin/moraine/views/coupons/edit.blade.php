@extends('admin::layouts.app')

@section('title', "Coupon Edit - {$coupon->code}")

@section('body')
<form action="{{ route('admin.coupons.update', ['coupon' => $coupon->id]) }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PUT')
    <div class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin::input 
                name="coupon_code"
                type="text"
                label="{{ __('admin/coupons.code_label') }}"
                helper="{{ __('admin/coupons.code_helper') }}"
                value="{{ old('coupon_code', $coupon->code) }}"
                required 
            />
            <x-admin::select 
                name="coupon_type"
                label="{{ __('admin/coupons.type_label') }}"
                helper="{{ __('admin/coupons.type_helper') }}"
                value="{{ old('coupon_type', $coupon->type) }}"
                required
            >
                <option value="percentage" {{ old('coupon_type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Percentage</option>
                <option value="fixed_amount" {{ old('coupon_type', $coupon->type) === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount</option>
            </x-admin::select>
            <x-admin::input 
                name="coupon_value"
                type="number"
                label="{{ __('admin/coupons.value_label') }}"
                helper="{{ __('admin/coupons.value_helper') }}"
                value="{{ old('coupon_value', $coupon->value) }}"
                required 
            />
            <x-admin::multiselect
                name="coupon_billing_cycles"
                label="{{ __('admin/coupons.billing_cycles_label') }}"
                helper="{{ __('admin/coupons.billing_cycles_helper') }}"
                :options="$billingCycleOptions"
                :selected="old('coupon_billing_cycles', $coupon->billing_cycles)"
            />
        </div>
        <x-admin::multiselect
            name="coupon_packages"
            label="{{ __('admin/coupons.packages_label') }}"
            helper="{{ __('admin/coupons.packages_helper') }}"
            :options="$packageOptions"
            :selected="old('coupon_packages', $coupon->packages->pluck('id')->toArray())"
        />
        <x-admin::multiselect
            name="coupon_tlds"
            label="{{ __('admin/coupons.tlds_label') }}"
            helper="{{ __('admin/coupons.tlds_helper') }}"
            :options="$tldOptions"
            :selected="old('coupon_tlds', $coupon->tlds->pluck('id')->toArray())"
        />
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin::input
                name="coupon_max_uses"
                type="number"
                label="{{ __('admin/coupons.max_uses_label') }}"
                helper="{{ __('admin/coupons.max_uses_helper') }}"
                value="{{ old('coupon_max_uses', $coupon->max_uses) }}"
            />
            <x-admin::input
                name="coupon_max_uses_per_user"
                type="number"
                label="{{ __('admin/coupons.max_uses_per_user_label') }}"
                helper="{{ __('admin/coupons.max_uses_per_user_helper') }}"
                value="{{ old('coupon_max_uses_per_user', $coupon->max_uses_per_user) }}"
            />
            <x-admin::input
                name="coupon_start_date"
                type="date"
                label="{{ __('admin/coupons.start_at_label') }}"
                helper="{{ __('admin/coupons.start_at_helper') }}"
                value="{{ old('coupon_start_date', $coupon->start_at?->format('Y-m-d')) }}"
            />
            <x-admin::input
                name="coupon_expires_date"
                type="date"
                label="{{ __('admin/coupons.expires_at_label') }}"
                helper="{{ __('admin/coupons.expires_at_helper') }}"
                value="{{ old('coupon_expires_date', $coupon->expires_at?->format('Y-m-d')) }}"
            />
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.coupons') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.save') }}</button>
    </div>
</form>
@endsection