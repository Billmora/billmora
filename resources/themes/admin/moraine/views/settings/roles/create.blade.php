@extends('admin::layouts.app')

@section('title', 'Role & Permission Create - Roles')

@section('body')
<form action="{{ route('admin.settings.roles.store') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input type="text" name="role_name" label="{{ __('admin/settings/role.role_name_label') }}" helper="{{ __('admin/settings/role.role_name_helper') }}" value="{{ old('role_name') }}" required />
        <x-admin::checkbox 
            class="gap-2 columns-1 md:columns-2 lg:columns-4 "
            name="role_permissions"
            label="{{ __('admin/settings/role.role_permissions_label') }}"
            helper="{{ __('admin/settings/role.role_permissions_helper') }}"
            :options="$permissions->map(fn($permission) => [
                    'name' => $permission->name,
                    'label' => implode(' ', array_reverse(array_map('ucfirst', explode('.', $permission->name))))
                ])->pluck('label','name')"
            :checked="old('role_permissions', [])"
        />
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.settings.roles') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
    </div>
</form>
@endsection