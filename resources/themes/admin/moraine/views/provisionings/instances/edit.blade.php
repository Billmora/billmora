@extends('admin::layouts.app')

@section('title', 'Instance Create')

@section('body')
<form action="{{ route('admin.provisionings.instance.update', ['driver' => $driver, 'instance' => $instance->id]) }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="instance_name"
            type="text"
            label="Name"
            helper="Lorem ipsum dolor sit amet consectetur adipisicing."
            value="{{ old('instance_name', $instance->name) }}"
            required 
        />
        <x-admin::toggle
            name="instance_active"
            label="Is Active?"
            helper="Lorem ipsum dolor sit amet consectetur adipisicing."
            checked="{{ old('instance_active', $instance->is_active) }}"
        />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        @foreach($formFields as $key => $field)
            @if(in_array($field['type'], ['text', 'email', 'url', 'number']))
                <x-admin::input 
                    name="{{ $key }}"
                    label="{{ $field['label'] ?? ucfirst($key) }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    type="{{ $field['type'] }}"
                    value="{{ old($key, $instance->config[$key]) }}"
                    required
                />
            @elseif($field['type'] === 'password')
                <x-admin::input 
                    name="{{ $key }}"
                    label="{{ $field['label'] ?? ucfirst($key) }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    type="{{ $field['type'] }}"
                    value="{{ old($key, $instance->config[$key]) }}"
                    required
                />
            @elseif($field['type'] === 'boolean')
                <x-admin::toggle
                    name="{{ $key }}"
                    label="{{ $field['label'] ?? ucfirst($key) }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    checked="{{ old($key, $instance->config[$key]) }}"
                    required
                />
                @elseif($field['type'] === 'select')
                <x-admin::select
                    name="{{ $key }}"
                    label="{{ $field['label'] ?? ucfirst($key) }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    required
                >
                    @foreach($field['options'] ?? [] as $optValue => $optLabel)
                        <option value="{{ $optValue }}" {{ old($key, $field['default'] ?? '') == $optValue ? 'selected' : '' }}>
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
@endsection