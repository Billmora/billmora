@extends('admin::layouts.app')

@section('title', "module Edit - {$module->name}")

@section('body')
<form 
    action="{{ route('admin.modules.update', $module) }}" 
    method="POST" 
    class="flex flex-col gap-5"
>
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="instance_name"
            type="text"
            label="{{ __('admin/modules.name_label') }}"
            helper="{{ __('admin/modules.name_helper') }}"
            value="{{ old('instance_name', $module->name) }}"
            required 
        />
        <x-admin::select
            name="instance_provider_display"
            label="{{ __('admin/modules.provider_label') }}"
            helper="{{ __('admin/modules.provider_helper') }}"
            required
            disabled
        >
            <option selected>{{ $module->provider }}</option>
        </x-admin::select>
        <x-admin::toggle
            name="instance_active"
            label="{{ __('admin/modules.is_active_label') }}"
            helper="{{ __('admin/modules.is_active_helper') }}"
            :checked="(bool)old('instance_active', $module->is_active)"
        />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        @foreach($schema as $key => $field)
            @if(in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                <x-admin::input 
                    name="configurations[{{ $module->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    type="{{ $field['type'] }}"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                    value="{{ old('configurations.'.$module->provider.'.'.$key, $module->config[$key] ?? $field['default'] ?? '') }}" 
                />
            @elseif($field['type'] === 'textarea')
                <x-admin::textarea
                    name="configurations[{{ $module->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                >{{ old('configurations.'.$module->provider.'.'.$key, $module->config[$key] ?? $field['default'] ?? '') }}</x-admin::textarea>
            @elseif($field['type'] === 'toggle')
                <x-admin::toggle
                    name="configurations[{{ $module->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :checked="(bool)old('configurations.'.$module->provider.'.'.$key, $module->config[$key] ?? $field['default'] ?? false)"
                />
            @elseif($field['type'] === 'select')
                <x-admin::select
                    name="configurations[{{ $module->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                >
                    @foreach($field['options'] ?? [] as $optValue => $optLabel)
                        <option 
                            value="{{ $optValue }}" 
                            {{ old('configurations.'.$module->provider.'.'.$key, $module->config[$key] ?? $field['default'] ?? '') == $optValue ? 'selected' : '' }}
                        >
                            {{ $optLabel }}
                        </option>
                    @endforeach
                </x-admin::select>
            @elseif($field['type'] === 'radio')
                <x-admin::radio.group 
                    name="configurations[{{ $module->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                >
                    @foreach($field['options'] as $optVal => $optLabel)
                        <x-admin::radio.option
                            name="configurations[{{ $module->provider }}][{{ $key }}]"
                            value="{{ $optVal }}"
                            label="{{ $optLabel }}"
                            :checked="old('configurations.'.$module->provider.'.'.$key, $module->config[$key] ?? $field['default'] ?? '') == $optVal" 
                        />
                    @endforeach
                </x-admin::radio.group>
            @elseif($field['type'] === 'checkbox')
                <x-admin::checkbox 
                    name="configurations[{{ $module->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :options="$field['options']" 
                    :checked="(array) old('configurations.'.$module->provider.'.'.$key, $module->config[$key] ?? $field['default'] ?? [])"
                />
            @endif
        @endforeach
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.modules') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    </div>
</form>
@endsection