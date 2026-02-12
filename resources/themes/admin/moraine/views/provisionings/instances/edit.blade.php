@extends('admin::layouts.app')

@section('title', 'Instance Create')

@section('body')
<form action="{{ route('admin.provisionings.instance.update', ['driver' => $instance->driver, 'instance' => $instance->id]) }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="instance_name"
            type="text"
            label="{{ __('admin/provisionings.instance.name_label') }}"
            helper="{{ __('admin/provisionings.instance.name_helper') }}"
            value="{{ old('instance_name', $instance->name) }}"
            required 
        />
        <x-admin::input 
            name="instance_provisioning"
            value="{{ $instance->driver }}"
            label="{{ __('admin/provisionings.instance.provisioning_label') }}"
            helper="{{ __('admin/provisionings.instance.provisioning_helper') }}"
            required
            disabled
        />
        <x-admin::toggle
            name="instance_active"
            label="{{ __('admin/provisionings.instance.is_active_label') }}"
            helper="{{ __('admin/provisionings.instance.is_active_helper') }}"
            checked="{{ old('instance_active', $instance->is_active) }}"
        />
    </div>
    <x-admin::modal.trigger type="button" modal="testConnectionModal" class="flex items-center gap-2 bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
        <x-lucide-cable class="w-auto h-5" />
        {{ __('admin/provisionings.instance.test_connection_label') }}
    </x-admin::modal.trigger>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        @foreach($formFields as $key => $field)
            @if(in_array($field['type'], ['text', 'email', 'url', 'number']))
                <x-admin::input 
                    name="{{ $key }}"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    type="{{ $field['type'] }}"
                    value="{{ old($key, $instance->config[$key] ?? '') }}"
                    :required="str_contains($field['rules'] ?? '', 'required')"
                />
            @elseif($field['type'] === 'password')
                <x-admin::input 
                    name="{{ $key }}"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    type="{{ $field['type'] }}"
                    value="{{ old($key, $instance->config[$key] ?? '') }}"
                    :required="str_contains($field['rules'] ?? '', 'required')"
                />
            @elseif($field['type'] === 'toggle')
                <x-admin::toggle
                    name="{{ $key }}"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :checked="(bool)old($key, $instance->config[$key] ?? false)"
                />
            @elseif($field['type'] === 'select')
                <x-admin::select
                    name="{{ $key }}"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :required="str_contains($field['rules'] ?? '', 'required')"
                >
                    @foreach($field['options'] ?? [] as $optValue => $optLabel)
                        <option value="{{ $optValue }}" {{ old($key, $instance->config[$key] ?? '') == $optValue ? 'selected' : '' }}>
                            {{ $optLabel }}
                        </option>
                    @endforeach
                </x-admin::select>
            @endif
        @endforeach
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.provisionings.instance', ['driver' => $driver]) }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.save') }}</button>
    </div>
</form>
<x-admin::modal.content
    modal="testConnectionModal"
    variant="danger"
    size="xl"
    position="centered"
    title="{{ __('common.confirm_modal_title')}}"
    description="{{ __('common.confirm_modal_description', ['item' => 'Test Connection']) }}"
>
    <form action="{{ route('admin.provisionings.instance.test', ['driver' => $instance->driver, 'instance' => $instance->id]) }}" method="POST">
        @csrf
        <div class="flex justify-end gap-2 mt-4">
            <x-admin::modal.trigger 
                type="button" 
                variant="close" 
                class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
            >
                {{ __('common.cancel') }}
            </x-admin::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.submit') }}
            </button>
        </div>
    </form>
</x-admin::modal.content>
@endsection