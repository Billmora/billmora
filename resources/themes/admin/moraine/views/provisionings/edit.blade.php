@extends('admin::layouts.app')

@section('title', "Provisioning Edit - " . $provisioning->name)

@section('body')
<form 
    action="{{ route('admin.provisionings.update', $provisioning) }}" 
    method="POST" 
    class="flex flex-col gap-5"
    enctype="multipart/form-data"
>
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="instance_name"
            type="text"
            label="{{ __('admin/provisionings.name_label') }}"
            helper="{{ __('admin/provisionings.name_helper') }}"
            value="{{ old('instance_name', $provisioning->name) }}"
            required 
        />
        <x-admin::select
            name="instance_provider_display"
            label="{{ __('admin/provisionings.provider_label') }}"
            helper="{{ __('admin/provisionings.provider_helper') }}"
            required
            disabled
        >
            <option selected>{{ $provisioning->provider }}</option>
        </x-admin::select>

        <x-admin::toggle
            name="instance_active"
            label="{{ __('admin/provisionings.is_active_label') }}"
            helper="{{ __('admin/provisionings.is_active_helper') }}"
            :checked="(bool)old('instance_active', $provisioning->is_active)"
        />
    </div>
    <x-admin::modal.trigger 
        modal="testConnectionModal"
        type="button"
        class="flex items-center gap-2 bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
    >
        <x-lucide-cable class="w-auto h-5" />
        {{ __('admin/provisionings.test_connection') }}
    </x-admin::modal.trigger>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        @foreach($schema as $key => $field)
            @if(in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                <x-admin::input 
                    name="configurations[{{ $provisioning->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    type="{{ $field['type'] }}"
                    :required="str_contains($field['rules'] ?? '', 'required')"
                    value="{{ old('configurations.'.$provisioning->provider.'.'.$key, $provisioning->config[$key] ?? $field['default'] ?? '') }}" 
                />
            @elseif($field['type'] === 'textarea')
                    <x-admin::textarea
                        name="configurations[{{ $provisioning->provider }}][{{ $key }}]"
                        label="{{ $field['label'] }}"
                        helper="{{ $field['helper'] ?? '' }}"
                        :required="str_contains($field['rules'] ?? '', 'required')"
                    >{{ old('configurations.'.$provisioning->provider.'.'.$key, $provisioning->config[$key] ?? $field['default'] ?? '') }}</x-admin::textarea>
            @elseif($field['type'] === 'toggle')
                <x-admin::toggle
                    name="configurations[{{ $provisioning->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :checked="(bool)old('configurations.'.$provisioning->provider.'.'.$key, $provisioning->config[$key] ?? $field['default'] ?? false)"
                />
            @elseif($field['type'] === 'select')
                <x-admin::select
                    name="configurations[{{ $provisioning->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :required="str_contains($field['rules'] ?? '', 'required')"
                >
                    @foreach($field['options'] ?? [] as $optValue => $optLabel)
                        <option 
                            value="{{ $optValue }}" 
                            {{ old('configurations.'.$provisioning->provider.'.'.$key, $provisioning->config[$key] ?? $field['default'] ?? '') == $optValue ? 'selected' : '' }}
                        >
                            {{ $optLabel }}
                        </option>
                    @endforeach
                </x-admin::select>
            @elseif($field['type'] === 'radio')
                <x-admin::radio.group 
                    name="configurations[{{ $provisioning->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :required="str_contains($field['rules'] ?? '', 'required')"
                >
                    @foreach($field['options'] as $optVal => $optLabel)
                        <x-admin::radio.option
                            name="configurations[{{ $provisioning->provider }}][{{ $key }}]"
                            value="{{ $optVal }}"
                            label="{{ $optLabel }}"
                            :checked="old('configurations.'.$provisioning->provider.'.'.$key, $provisioning->config[$key] ?? $field['default'] ?? '') == $optVal" 
                        />
                    @endforeach
                </x-admin::radio.group>
            @elseif($field['type'] === 'checkbox')
                <x-admin::checkbox 
                    name="configurations[{{ $provisioning->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :options="$field['options']" 
                    :checked="(array) old('configurations.'.$provisioning->provider.'.'.$key, $provisioning->config[$key] ?? $field['default'] ?? [])"
                />
            @endif
        @endforeach
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.provisionings') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
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
    description="{{ __('common.confirm_modal_description', ['item' => __('admin/provisionings.test_connection')]) }}"
>
    <form action="{{ route('admin.provisionings.test', ['provisioning' => $provisioning->id]) }}" method="POST">
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