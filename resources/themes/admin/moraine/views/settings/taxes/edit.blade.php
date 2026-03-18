@extends('admin::layouts.app')

@section('title', "Tax Edit - {$tax->name}")

@section('body')
<form action="{{ route('admin.settings.taxes.update', ['tax' => $tax->id]) }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PUT')
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-none md:grid-cols-2 gap-4">
            <x-admin::input type="text" name="tax_name" label="{{ __('admin/settings/tax.tax_name_label') }}" helper="{{ __('admin/settings/tax.tax_name_helper') }}" value="{{ old('tax_name', $tax->name) }}" required />
            <x-admin::input type="text" name="tax_value" label="{{ __('admin/settings/tax.tax_value_label') }}" helper="{{ __('admin/settings/tax.tax_value_helper') }}" value="{{ old('tax_value', $tax->value) }}" required />
        </div>
        <div class="grid gap-4">
            <x-admin::select name="tax_country" label="{{ __('admin/settings/tax.tax_country_label') }}" helper="{{ __('admin/settings/tax.tax_country_helper') }}">
                <option value="">Global</option>
                @foreach(config('utils.countries') as $code => $name)
                    <option value="{{ $code }}" {{ old('tax_country', $tax->country) === $code ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </x-admin::select>
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.settings.taxes') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.update') }}</button>
    </div>
</form>
@endsection
