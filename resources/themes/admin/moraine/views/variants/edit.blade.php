@extends('admin::layouts.app')

@section('title', "Variant Edit")

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.variants.edit', ['id' => $variant->id]),
                'icon' => 'lucide-boxes',
                'label' => __('admin/variants.tabs.summary'),
            ],
            [
                'route' => route('admin.variants.options', ['id' => $variant->id]),
                'icon' => 'lucide-codesandbox',
                'label' => __('admin/variants.tabs.options'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <form action="{{ route('admin.variants.update', ['id' => $variant->id]) }}" method="POST" class="flex flex-col gap-5">
        @csrf
        @method('PUT')
        <div class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl space-y-4">
            <div class="grid grid-cols-none md:grid-cols-2 gap-5">
                <x-admin::input 
                    name="variant_name"
                    type="text"
                    label="{{ __('admin/variants.name_label') }}"
                    helper="{{ __('admin/variants.name_helper') }}"
                    value="{{ old('variant_name', $variant->name) }}"
                    required 
                />
                <x-admin::select 
                    name="variant_type"
                    label="{{ __('admin/variants.type_label') }}"
                    helper="{{ __('admin/variants.type_helper') }}"
                    required
                >
                    <option value="select" {{ old('variant_type', $variant->type) === 'select' ? 'selected' : '' }}>Select</option>
                    <option value="radio" {{ old('variant_type', $variant->type) === 'radio' ? 'selected' : '' }}>Radio</option>
                    <option value="slider" {{ old('variant_type', $variant->type) === 'slider' ? 'selected' : '' }}>Slider</option>
                    <option value="checkbox" {{ old('variant_type', $variant->type) === 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                </x-admin::select>
            </div>
            <x-admin::input 
                name="variant_code"
                type="text"
                label="{{ __('admin/variants.code_label') }}"
                helper="{{ __('admin/variants.code_helper') }}"
                value="{{ old('variant_code', $variant->code) }}"
                required
            />
            <div class="grid grid-cols-none md:grid-cols-2 gap-5">
                <x-admin::radio.group 
                    name="variant_status"
                    label="{{ __('admin/variants.status_label') }}"
                    helper="{{ __('admin/variants.status_helper') }}"
                    required
                >
                    <x-admin::radio.option
                        name="variant_status"
                        value="visible"
                        label="{{ __('admin/variants.status_options.visible') }}"
                        :checked="old('variant_status', $variant->status) === 'visible'" />
                    <x-admin::radio.option
                        name="variant_status"
                        value="hidden"
                        label="{{ __('admin/variants.status_options.hidden') }}"
                        :checked="old('variant_status', $variant->status) === 'hidden'" />
                </x-admin::radio.group>
                <x-admin::toggle 
                    name="variant_is_upgradable"
                    label="{{ __('admin/variants.is_upgradable_label') }}"
                    helper="{{ __('admin/variants.is_upgradable_helper') }}"
                    :checked="old('variant_is_upgradable', $variant->is_upgradable)" />
            </div>
            <x-admin::multiselect
                name="variant_packages"
                :options="$packageOptions"
                :selected="old('variant_packages', $variant->packages->pluck('id')->toArray())"
                label="{{ __('admin/variants.package_label') }}"
                helper="{{ __('admin/variants.package_helper') }}"
                required
            />
        </div>
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.variants') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.save') }}</button>
        </div>
    </form>
</div>
@endsection