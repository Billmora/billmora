@extends('admin::layouts.app')

@section('title', "Registrar Edit - {$registrar->name}")

@section('body')
<form 
    action="{{ route('admin.registrars.update', $registrar) }}" 
    method="POST" 
    class="flex flex-col gap-5"
>
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="instance_name"
            type="text"
            label="{{ __('admin/registrars.name_label') }}"
            helper="{{ __('admin/registrars.name_helper') }}"
            value="{{ old('instance_name', $registrar->name) }}"
            required 
        />
        <x-admin::select
            name="instance_provider_display"
            label="{{ __('admin/registrars.provider_label') }}"
            helper="{{ __('admin/registrars.provider_helper') }}"
            required
            disabled
        >
            <option selected>{{ $registrar->provider }}</option>
        </x-admin::select>

        <x-admin::toggle
            name="instance_active"
            label="{{ __('admin/registrars.is_active_label') }}"
            helper="{{ __('admin/registrars.is_active_helper') }}"
            :checked="(bool)old('instance_active', $registrar->is_active)"
        />
    </div>
    <x-admin::modal.trigger 
        modal="testConnectionModal"
        type="button"
        class="flex items-center gap-2 bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
    >
        <x-lucide-cable class="w-auto h-5" />
        {{ __('admin/registrars.test_connection') }}
    </x-admin::modal.trigger>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        @foreach($schema as $key => $field)
            @if(in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                <x-admin::input 
                    name="configurations[{{ $registrar->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    type="{{ $field['type'] }}"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                    value="{{ old('configurations.'.$registrar->provider.'.'.$key, $registrar->config[$key] ?? $field['default'] ?? '') }}" 
                />
            @elseif($field['type'] === 'textarea')
                <x-admin::textarea
                    name="configurations[{{ $registrar->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                >{{ old('configurations.'.$registrar->provider.'.'.$key, $registrar->config[$key] ?? $field['default'] ?? '') }}</x-admin::textarea>
            @elseif($field['type'] === 'toggle')
                <x-admin::toggle
                    name="configurations[{{ $registrar->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :checked="(bool)old('configurations.'.$registrar->provider.'.'.$key, $registrar->config[$key] ?? $field['default'] ?? false)"
                />
            @elseif($field['type'] === 'select')
                <x-admin::select
                    name="configurations[{{ $registrar->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                >
                    @foreach($field['options'] ?? [] as $optValue => $optLabel)
                        <option 
                            value="{{ $optValue }}" 
                            {{ old('configurations.'.$registrar->provider.'.'.$key, $registrar->config[$key] ?? $field['default'] ?? '') == $optValue ? 'selected' : '' }}
                        >
                            {{ $optLabel }}
                        </option>
                    @endforeach
                </x-admin::select>
            @endif
        @endforeach
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.registrars') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    </div>
</form>
<x-admin::modal.content
    modal="testConnectionModal"
    variant="info"
    size="xl"
    position="centered"
    title="{{ __('common.confirm_modal_title')}}"
    description="{{ __('common.confirm_modal_description', ['item' => __('admin/registrars.test_connection')]) }}"
>
    <form action="{{ route('admin.registrars.test', ['registrar' => $registrar->id]) }}" method="POST">
            @csrf
            <div class="flex justify-end gap-2 mt-4">
                <x-admin::modal.trigger 
                    type="button" 
                    variant="close" 
                    class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
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
