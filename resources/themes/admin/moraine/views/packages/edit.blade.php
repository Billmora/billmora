@extends('admin::layouts.app')

@section('title', "Package Edit - {$package->name}")

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.packages.edit', ['package' => $package->id]),
                'icon' => 'lucide-package',
                'label' => __('admin/packages.tabs.summary'),
            ],
            [
                'route' => route('admin.packages.pricing', ['package' => $package->id]),
                'icon' => 'lucide-badge-cent',
                'label' => __('admin/packages.tabs.pricing'),
            ],
            [
                'route' => route('admin.packages.provisioning', ['package' => $package->id]),
                'icon' => 'lucide-plug',
                'label' => 'Provisioning',
            ],
            [
                'route' => route('admin.packages.scaling', ['package' => $package->id]),
                'icon' => 'lucide-arrow-up-down',
                'label' => __('admin/packages.tabs.scaling'),
            ],
        ]" 
        active="{{ request()->url() }}" />
    <form action="{{ route('admin.packages.update', ['package' => $package->id]) }}" method="POST" class="flex flex-col gap-5" enctype="multipart/form-data">
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
                <div x-data="{ removed: false }">
                    @if ($package->icon)
                        <div x-show="!removed" class="grid gap-2 mb-4">
                            <label class="text-slate-600 font-semibold text-sm">{{ __('admin/packages.icon_label') }}</label>
                            <div class="grid gap-3 p-3 bg-billmora-bg border-2 border-billmora-2 rounded-xl">
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ Storage::url($package->icon) }}"
                                    alt="{{ $package->name }}"
                                    class="w-14 h-14 object-contain rounded-lg bg-white p-1 border border-billmora-2 shrink-0"
                                >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-700 truncate">{{ basename($package->icon) }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ __('admin/packages.icon_helper') }}</p>
                                </div>
                            </div>
                            <button type="button" x-on:click="removed = true"
                                class="w-full flex items-center justify-center gap-2 p-2 text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-lg transition-colors duration-150 text-sm font-semibold cursor-pointer"
                            >
                                <x-lucide-trash-2 class="w-4 h-4" />
                                {{ __('common.delete') }}
                            </button>
                            <input type="hidden" name="remove_package_icon" x-bind:value="removed ? 1 : 0">
                        </div>
                        </div>
                    @endif
                    <div x-show="removed || !{{ $package->icon ? 'true' : 'false' }}">
                        <x-admin::input
                            type="file"
                            name="package_icon"
                            label="{{ __('admin/packages.icon_label') }}{{ $package->icon ? ' (' . __('common.update') . ')' : '' }}"
                            helper="{{ __('admin/packages.icon_helper') }}"
                        />
                    </div>
                </div>

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
                <x-admin::select
                    name="package_allow_quantity"
                    label="{{ __('admin/packages.allow_quantity_label') }}"
                    helper="{{ __('admin/packages.allow_quantity_helper') }}"
                    required
                >
                    @foreach (['single', 'multiple'] as $quantity)
                        <option value="{{ $quantity }}" {{ old('package_allow_quantity', $package->allow_quantity) === $quantity ? 'selected' : '' }}>{{ ucwords($quantity) }}</option>
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
</div>
@endsection
