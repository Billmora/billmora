@extends('admin::layouts.app')

@section('title', "Catalog Create")

@section('body')
<form action="{{ route('admin.catalogs.store') }}" method="POST" class="flex flex-col gap-5" enctype="multipart/form-data">
    @csrf
    <div class="flex flex-col lg:flex-row gap-5">
        <div 
            x-data="{
                name: '{{ old('catalog_name') }}',
                slug: '{{ old('catalog_slug') }}',
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
            <x-admin::input 
                type="text"
                name="catalog_name"
                x-model="name"
                label="{{ __('admin/catalogs.name_label') }}"
                helper="{{ __('admin/catalogs.name_helper') }}"
                required
            />
            <div class="grid">
                <div class="flex gap-1 mb-1">
                    <label for="catalog_slug" class="text-slate-600 font-semibold">
                        {{ __('admin/catalogs.slug_label') }}
                    </label>
                    <span class="text-slate-600">
                        {{ __('common.symbol_required') }}
                    </span>
                </div>
                <div class="flex">
                    <div>
                        <x-admin::input 
                            type="text"
                            name="prefix_slug"
                            value="{{ url('/') }}/store/"
                            disabled
                        />
                    </div>
                    <x-admin::input 
                        type="text"
                        name="catalog_slug"
                        x-model="slug"
                        x-bind:value="edited ? slug : generatedSlug"
                        x-on:input="edited = true"
                    />
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ __('admin/catalogs.slug_helper') }}</p>
            </div>
            <x-admin::textarea
                name="catalog_description"
                label="{{ __('admin/catalogs.description_label') }}"
                helper="{{ __('admin/catalogs.description_helper') }}"
                rows="6"
                required
            >{{ old('catalog_description') }}</x-admin::textarea>
        </div>
        <div class="w-full lg:w-1/3 h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::input
                type="file"
                name="catalog_icon"
                label="{{ __('admin/catalogs.icon_label') }}"
                helper="{{ __('admin/catalogs.icon_helper') }}"
            />
            <x-admin::radio.group 
                name="catalog_status"
                label="{{ __('admin/catalogs.status_label') }}"
                helper="{{ __('admin/catalogs.status_helper') }}"
                required
            >
                <x-admin::radio.option
                    name="catalog_status"
                    value="visible"
                    label="{{ __('admin/catalogs.status_options.visible') }}"
                    :checked="old('catalog_status', 'visible') === 'visible'" />
                <x-admin::radio.option
                    name="catalog_status"
                    value="hidden"
                    label="{{ __('admin/catalogs.status_options.hidden') }}"
                    :checked="old('catalog_status') === 'hidden'" />
            </x-admin::radio.group>
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.catalogs') }}" 
            class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 
                   px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors 
                   ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" 
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white 
                   rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.create') }}
        </button>
    </div>
</form>
@endsection
