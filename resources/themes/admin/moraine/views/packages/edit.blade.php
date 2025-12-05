@extends('admin::layouts.app')

@section('title', "Package Create")

@section('body')
<div class="flex flex-col gap-5">
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    @if (session('error'))
        <x-admin::alert variant="danger" title="{{ session('error') }}" />
    @endif
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.packages.edit', ['id' => $package->id]),
                'icon' => 'lucide-package',
                'label' => __('admin/packages.tabs.summary'),
            ],
            [
                'route' => route('admin.packages.pricing', ['id' => $package->id]),
                'icon' => 'lucide-badge-cent',
                'label' => __('admin/packages.tabs.pricing'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <form action="{{ route('admin.packages.update', ['id' => $package->id]) }}" method="POST" class="flex flex-col gap-5" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="flex flex-col lg:flex-row gap-5">
            <div
                x-data="{
                    name: '{{ old('package_name', $package->name) }}',
                    slug: '{{ old('package_slug', $package->slug) }}',
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
                            {{ old('catalog_id', $package->catalog_id) == $catalog->id ? 'selected' : '' }}
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
                >{{ old('package_description', $package->description) }}</x-admin::textarea>
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
                    value="{{ old('package_stock', $package->stock) }}"
                    required
                />
                <x-admin::input
                    type="number"
                    min="-1"
                    name="package_per_user_limit"
                    label="{{ __('admin/packages.per_user_limit_label') }}"
                    helper="{{ __('admin/packages.per_user_limit_helper') }}"
                    value="{{ old('package_per_user_limit', $package->per_user_limit) }}"
                    required
                />
                <x-admin::toggle
                    name="package_allow_cancellation"
                    label="{{ __('admin/packages.allow_cancellation_label') }}"
                    helper="{{ __('admin/packages.allow_cancellation_helper') }}"
                    :checked="old('package_allow_cancellation', $package->allow_cancellation)"
                />
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
                        :checked="old('package_status', $package->status) === 'visible'" />
                    <x-admin::radio.option
                        name="package_status"
                        value="hidden"
                        label="{{ __('admin/packages.status_options.hidden') }}"
                        :checked="old('package_status', $package->status) === 'hidden'" />
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
                {{ __('common.save') }}
            </button>
        </div>
    </form>
</div>
@endsection
