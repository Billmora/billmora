@extends('admin::layouts.app')

@section('title', "Catalog Edit - $catalog->name")

@section('body')
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
            <div x-data="{ removed: false }">
                @if ($catalog->icon)
                    <div x-show="!removed" class="grid gap-2 mb-4">
                        <label class="text-slate-600 font-semibold text-sm">{{ __('admin/catalogs.icon_label') }}</label>
                        <div class="grid gap-3 p-3 bg-billmora-bg border-2 border-billmora-2 rounded-xl">
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ Storage::url($catalog->icon) }}"
                                    alt="{{ $catalog->name }}"
                                    class="w-14 h-14 object-contain rounded-lg bg-white p-1 border border-billmora-2 shrink-0"
                                >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-700 truncate">{{ basename($catalog->icon) }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ __('admin/catalogs.icon_helper') }}</p>
                                </div>
                            </div>
                            <button type="button" x-on:click="removed = true"
                                class="w-full flex items-center justify-center gap-2 p-2 text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-lg transition-colors duration-150 text-sm font-semibold cursor-pointer"
                            >
                                <x-lucide-trash-2 class="w-4 h-4" />
                                {{ __('common.delete') }}
                            </button>
                            <input type="hidden" name="remove_catalog_icon" x-bind:value="removed ? 1 : 0">
                        </div>
                    </div>
                @endif
                <div x-show="removed || !{{ $catalog->icon ? 'true' : 'false' }}">
                    <x-admin::input
                        type="file"
                        name="catalog_icon"
                        label="{{ __('admin/catalogs.icon_label') }}{{ $catalog->icon ? ' (' . __('common.update') . ')' : '' }}"
                        helper="{{ __('admin/catalogs.icon_helper') }}"
                    />
                </div>
            </div>

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
            class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 
                   px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors 
                   ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" 
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white 
                   rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    </div>
</form>
@endsection
