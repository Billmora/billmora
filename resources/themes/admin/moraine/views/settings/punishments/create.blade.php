@extends('admin::layouts.app')

@section('title', 'Punishment Create - Punishments')

@section('body')
<form action="{{ route('admin.settings.punishments.store') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-none md:grid-cols-2 gap-4">
            <x-admin::singleselect 
                name="user_id"
                label="{{ __('admin/settings/punishment.punishment_user_label') }}"
                helper="{{ __('admin/settings/punishment.punishment_user_helper') }}"
                :options="$userOptions"
                :selected="old('user_id')"
                required
            />

            <x-admin::select name="status" label="{{ __('admin/settings/punishment.punishment_status_label') }}" helper="{{ __('admin/settings/punishment.punishment_status_helper') }}" required>
                <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </x-admin::select>
        </div>
        <div class="grid gap-4">
            <x-admin::textarea name="reason" label="{{ __('admin/settings/punishment.punishment_reason_label') }}" helper="{{ __('admin/settings/punishment.punishment_reason_helper') }}" value="{{ old('reason') }}" required />
        </div>
        <div class="grid grid-cols-none md:grid-cols-2 gap-4">
            <x-admin::input 
                type="datetime-local" 
                name="expires_at" 
                label="{{ __('admin/settings/punishment.punishment_expires_at_label') }}" 
                helper="{{ __('admin/settings/punishment.punishment_expires_at_helper') }}" 
                value="{{ old('expires_at') }}" 
            />
        </div>
        <div class="grid grid-cols-none md:grid-cols-2 gap-4">
            <x-admin::toggle 
                name="terminate_services" 
                label="{{ __('admin/settings/punishment.punishment_terminate_services_label') }}" 
                helper="{{ __('admin/settings/punishment.punishment_terminate_services_helper') }}" 
                checked="{{ old('terminate_services') }}" 
            />
            
            <x-admin::toggle 
                name="notify_user" 
                label="{{ __('admin/settings/punishment.punishment_notify_label') }}" 
                helper="{{ __('admin/settings/punishment.punishment_notify_helper') }}" 
                checked="{{ old('notify_user') }}" 
            />
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.settings.punishments') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
    </div>
</form>
@endsection
