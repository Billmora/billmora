@extends('admin::layouts.app')

@section('title', "Currency Edit - {$currency->code}")

@section('body')
<form action="{{ route('admin.settings.currencies.update', ['id' => $currency->id]) }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-admin::input type="text" name="currency_code" label="{{ __('admin/settings/currency.currency_code_label') }}" helper="{{ __('admin/settings/currency.currency_code_helper') }}" value="{{ old('currency_code', $currency->code) }}" required />
            <x-admin::input type="text" name="currency_prefix" label="{{ __('admin/settings/currency.currency_prefix_label') }}" helper="{{ __('admin/settings/currency.currency_prefix_helper') }}" value="{{ old('currency_prefix', $currency->prefix) }}" />
            <x-admin::input type="text" name="currency_suffix" label="{{ __('admin/settings/currency.currency_suffix_label') }}" helper="{{ __('admin/settings/currency.currency_suffix_helper') }}" value="{{ old('currency_suffix', $currency->suffix) }}" />
        </div>
        <div class="grid grid-cols-none md:grid-cols-2 gap-4">
            <x-admin::select name="currency_format" label="{{ __('admin/settings/currency.currency_format_label') }}" helper="{{ __('admin/settings/currency.currency_format_helper') }}" required>
                <option value="1234.56" {{ old('currency_format', $currency->format) == '1234.56' ? 'selected' : '' }}>1234.56</option>
                <option value="1,234.56" {{ old('currency_format', $currency->format) == '1,234.56' ? 'selected' : '' }}>1,234.56</option>
                <option value="1.234,56" {{ old('currency_format', $currency->format) == '1.234,56' ? 'selected' : '' }}>1.234,56</option>
                <option value="1,234" {{ old('currency_format', $currency->format) == '1,234' ? 'selected' : '' }}>1,234</option>
            </x-admin::select>
            <x-admin::input type="text" name="currency_base_rate" label="{{ __('admin/settings/currency.currency_base_rate_label') }}" helper="{{ __('admin/settings/currency.currency_base_rate_helper') }}" value="{{ old('currency_base_rate', $currency->base_rate) }}" required />
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.settings.currencies') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.update') }}</button>
    </div>
</form>
@endsection