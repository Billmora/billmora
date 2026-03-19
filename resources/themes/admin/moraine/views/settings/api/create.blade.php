@extends('admin::layouts.app')

@section('title', 'API Settings - Create Token')

@section('body')
<form action="{{ route('admin.settings.api.store') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    <div class="grid md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::input 
                type="text" 
                name="token_name"
                label="{{ __('admin/settings/api.token_name_label') }}"
                helper="{{ __('admin/settings/api.token_name_helper') }}"
                value="{{ old('token_name') }}" 
                required 
            />
            <x-admin::input 
                type="number" 
                name="token_rate_limit"
                label="{{ __('admin/settings/api.rate_limit_label') }}"
                helper="{{ __('admin/settings/api.rate_limit_helper') }}"
                value="{{ old('token_rate_limit', 60) }}"
                min="1" 
                max="1000" 
                required 
            />
        </div>
        <div class="flex flex-col gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::input 
                type="datetime-local" 
                name="token_expires_at"
                label="{{ __('admin/settings/api.expires_at_label') }}"
                helper="{{ __('admin/settings/api.expires_at_helper') }}"
                value="{{ old('token_expires_at') }}" 
            />
            <x-admin::textarea 
                rows="3" 
                name="token_whitelist_ips"
                label="{{ __('admin/settings/api.whitelist_ips_label') }}"
                helper="{{ __('admin/settings/api.whitelist_ips_helper') }}"
            >{{ old('token_whitelist_ips') }}</x-admin::textarea>
        </div>
    </div>
    <div class="bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div
            id="token_permissions_list"
            x-data="{
                allSelected: false,
                toggleSelect() {
                    let checkboxes = $el.querySelectorAll('#token_permissions_list input[type=checkbox]');
                    this.allSelected = !this.allSelected;
                    checkboxes.forEach(cb => cb.checked = this.allSelected);
                }
            }"
            x-on:change="allSelected = Array.from($el.querySelectorAll('#token_permissions_list input[type=checkbox]')).every(cb => cb.checked)"
        >
            <div class="flex justify-end mb-2">
                <button
                    type="button"
                    x-on:click="toggleSelect"
                    class="text-billmora-primary-500 hover:text-billmora-primary-600 font-semibold underline cursor-pointer transition duration-300"
                    x-text="allSelected ? '{{ __('common.deselect_all') }}' : '{{ __('common.select_all') }}'"
                >
                </button>
            </div>
            <x-admin::checkbox
                class="gap-2 columns-1 md:columns-2 lg:columns-4"
                name="token_permissions"
                label="{{ __('admin/settings/api.permissions_label') }}"
                helper="{{ __('admin/settings/api.permissions_helper') }}"
                :options="collect($permissions)->map(fn($permission) => [
                        'name' => $permission,
                        'label' => implode(' ', array_reverse(array_map('ucfirst', explode('.', $permission))))
                    ])->pluck('label','name')"
                :checked="old('token_permissions', [])"
            />
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.settings.api') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
    </div>
</form>
@endsection
