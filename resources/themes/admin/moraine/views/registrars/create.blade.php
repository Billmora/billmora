@extends('admin::layouts.app')

@section('title', 'Registrar Create')

@section('body')
<form 
    action="{{ route('admin.registrars.store') }}" 
    method="POST" 
    class="flex flex-col gap-5"
    x-data="{ selectedProvider: '{{ old('instance_provider', '') }}' }"
>
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="instance_name"
            type="text"
            label="{{ __('admin/registrars.name_label') }}"
            helper="{{ __('admin/registrars.name_helper') }}"
            value="{{ old('instance_name') }}"
            required 
        />
        <x-admin::select
            name="instance_provider"
            label="{{ __('admin/registrars.provider_label') }}"
            helper="{{ __('admin/registrars.provider_helper') }}"
            required
            x-model="selectedProvider"
        >
            @foreach($providers as $p)
                <option value="{{ $p['provider'] }}" {{ old('instance_provider') == $p['provider'] ? 'selected' : '' }}>
                    {{ $p['name'] }}
                </option>
            @endforeach
        </x-admin::select>

        <x-admin::toggle
            name="instance_active"
            label="{{ __('admin/registrars.is_active_label') }}"
            helper="{{ __('admin/registrars.is_active_helper') }}"
            :checked="old('instance_active', true)"
        />
    </div>
    <div x-show="selectedProvider">
    @foreach($providers as $provider)
        <div 
            class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl"
            x-show="selectedProvider === '{{ $provider['provider'] }}'"
        >
            @foreach($provider['schema'] as $key => $field)
                @if(in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                    <x-admin::input 
                        name="configurations[{{ $provider['provider'] }}][{{ $key }}]"
                        label="{{ $field['label'] }}"
                        helper="{{ $field['helper'] ?? '' }}"
                        type="{{ $field['type'] }}"
                        placeholder="{{ $field['placeholder'] ?? '' }}"
                        :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                        value="{{ old('configurations.'.$provider['provider'].'.'.$key, $field['default'] ?? '') }}" 
                    />
                @elseif($field['type'] === 'textarea')
                    <x-admin::textarea
                        name="configurations[{{ $provider['provider'] }}][{{ $key }}]"
                        label="{{ $field['label'] }}"
                        helper="{{ $field['helper'] ?? '' }}"
                        placeholder="{{ $field['placeholder'] ?? '' }}"
                        :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                    >{{ old('configurations.'.$provider['provider'].'.'.$key, $field['default'] ?? '') }}</x-admin::textarea>
                @elseif($field['type'] === 'toggle')
                    <x-admin::toggle
                        name="configurations[{{ $provider['provider'] }}][{{ $key }}]"
                        label="{{ $field['label'] }}"
                        helper="{{ $field['helper'] ?? '' }}"
                        :checked="(bool)old('configurations.'.$provider['provider'].'.'.$key, $field['default'] ?? false)"
                    />
                @elseif($field['type'] === 'select')
                    <x-admin::select
                        name="configurations[{{ $provider['provider'] }}][{{ $key }}]"
                        label="{{ $field['label'] }}"
                        helper="{{ $field['helper'] ?? '' }}"
                        required="{{ str_contains($field['rules'] ?? '', 'required') }}"
                    >
                        @foreach($field['options'] ?? [] as $optValue => $optLabel)
                            <option 
                                value="{{ $optValue }}" 
                                {{ old('configurations.'.$provider['provider'].'.'.$key, $field['default'] ?? '') == $optValue ? 'selected' : '' }}
                            >
                                {{ $optLabel }}
                            </option>
                        @endforeach
                    </x-admin::select>
                @endif
            @endforeach
        </div>
    @endforeach
</div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.registrars') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.create') }}
        </button>
    </div>
</form>
@endsection
