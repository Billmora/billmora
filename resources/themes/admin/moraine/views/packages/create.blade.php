@extends('admin::layouts.app')

@section('title', "Package Create")

@section('body')
<form action="{{ route('admin.packages.store') }}" method="POST" class="flex flex-col gap-5" enctype="multipart/form-data">
    @csrf
    <div class="flex flex-col lg:flex-row gap-5">
        <div
            x-data="{
                name: '{{ old('package_name') }}',
                slug: '{{ old('package_slug') }}',
                edited: false,
                get generatedSlug() {
                    return this.name
                        .toLowerCase()
                        .trim()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-');
                }
            }"
            class="w-full lg:w-2/3 h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl"
        >
            <x-admin::select 
                name="catalog_id"
                label="{{ __('admin/packages.catalog_label') }}"
                helper="{{ __('admin/packages.catalog_helper') }}"
                required
            >
                @foreach ($catalogs as $catalog)
                    <option
                        value="{{ $catalog->id }}"
                        {{ old('catalog_id') == $catalog->id ? 'selected' : '' }}
                    >
                        {{ $catalog->name }}
                    </option>
                @endforeach
            </x-admin::select>
            <x-admin::input 
                type="text"
                name="package_name"
                x-model="name"
                label="{{ __('admin/packages.name_label') }}"
                helper="{{ __('admin/packages.name_helper') }}"
                required
            />
            <x-admin::input 
                type="text"
                name="package_slug"
                label="{{ __('admin/packages.slug_label') }}"
                helper="{{ __('admin/packages.slug_helper') }}"
                x-model="slug"
                x-bind:value="edited ? slug : generatedSlug"
                x-on:input="edited = true"
                required
            />
            <x-admin::textarea
                name="package_description"
                label="{{ __('admin/packages.description_label') }}"
                helper="{{ __('admin/packages.description_helper') }}"
                rows="6"
                required
            >{{ old('package_description') }}</x-admin::textarea>
        </div>
        <div class="w-full lg:w-1/3 h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::input
                type="file"
                name="package_icon"
                label="{{ __('admin/packages.icon_label') }}"
                helper="{{ __('admin/packages.icon_helper') }}"
            />
            <x-admin::input
                type="number"
                min="-1"
                name="package_stock"
                label="{{ __('admin/packages.stock_label') }}"
                helper="{{ __('admin/packages.stock_helper') }}"
                value="{{ old('package_stock', -1) }}"
                required
            />
            <x-admin::input
                type="number"
                min="-1"
                name="package_per_user_limit"
                label="{{ __('admin/packages.per_user_limit_label') }}"
                helper="{{ __('admin/packages.per_user_limit_helper') }}"
                value="{{ old('package_per_user_limit', -1) }}"
                required
            />
            <x-admin::toggle
                name="package_allow_cancellation"
                label="{{ __('admin/packages.allow_cancellation_label') }}"
                helper="{{ __('admin/packages.allow_cancellation_helper') }}"
                :checked="old('package_allow_cancellation')"
            />
            <x-admin::select
                name="package_allow_quantity"
                label="{{ __('admin/packages.allow_quantity_label') }}"
                helper="{{ __('admin/packages.allow_quantity_helper') }}"
                required
            >
                @foreach (['single', 'multiple'] as $quantity)
                    <option value="{{ $quantity }}" {{ old('package_allow_quantity') === $quantity ? 'selected' : '' }}>{{ ucwords($quantity) }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::radio.group 
                name="package_status"
                label="{{ __('admin/packages.status_label') }}"
                helper="{{ __('admin/packages.status_helper') }}"
                required
            >
                <x-admin::radio.option
                    name="package_status"
                    value="visible"
                    label="{{ __('admin/packages.status_options.visible') }}"
                    :checked="old('package_status', 'visible') === 'visible'" />
                <x-admin::radio.option
                    name="package_status"
                    value="hidden"
                    label="{{ __('admin/packages.status_options.hidden') }}"
                    :checked="old('package_status') === 'hidden'" />
            </x-admin::radio.group>
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.packages') }}" 
            class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover 
                   px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors 
                   ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" 
            class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white 
                   rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.create') }}
        </button>
    </div>
</form>
@endsection
