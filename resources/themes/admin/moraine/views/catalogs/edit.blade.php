@extends('admin::layouts.app')

@section('title', "Catalog Edit - $catalog->name")

@section('body')
<div class="flex flex-col gap-5">
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    @if (session('error'))
        <x-admin::alert variant="danger" title="{{ session('error') }}" />
    @endif
    <form action="{{ route('admin.catalogs.update', $catalog->id) }}" method="POST" class="flex flex-col gap-5" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="flex flex-col lg:flex-row gap-5">
            <div 
                x-data="{
                    name: '{{ old('catalog_name', $catalog->name) }}',
                    slug: '{{ old('catalog_slug', $catalog->slug) }}',
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
                >{{ old('catalog_description', $catalog->description) }}</x-admin::textarea>
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
                        :checked="old('catalog_status', $catalog->status) === 'visible'" />
                    <x-admin::radio.option
                        name="catalog_status"
                        value="hidden"
                        label="{{ __('admin/catalogs.status_options.hidden') }}"
                        :checked="old('catalog_status', $catalog->status) === 'hidden'" />
                </x-admin::radio.group>
            </div>
        </div>
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.catalogs') }}" 
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
