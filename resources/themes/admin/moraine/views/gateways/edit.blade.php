@extends('admin::layouts.app')

@section('title', "Gateway Edit - {$gateway->name}")

@section('body')
<form 
    action="{{ route('admin.gateways.update', $gateway) }}" 
    method="POST" 
    class="flex flex-col gap-5"
>
    @csrf
    @method('PUT')
    <x-admin::alert variant="primary" title="{{ __('admin/gateways.webhook_url_title') }}">
        <p class="text-sm">{{ __('admin/gateways.webhook_url_description') }}</p>
        <div class="mt-2 w-full p-3 bg-white/50 border border-violet-200 rounded-lg flex items-center justify-between gap-4">
            <code class="text-violet-900 break-all font-mono text-sm select-all">{{ route('api.gateways.webhook', ['plugin' => $gateway->id]) }}</code>
        </div>
    </x-admin::alert>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="instance_name"
            type="text"
            label="{{ __('admin/gateways.name_label') }}"
            helper="{{ __('admin/gateways.name_helper') }}"
            value="{{ old('instance_name', $gateway->name) }}"
            required 
        />
        <x-admin::select
            name="instance_provider_display"
            label="{{ __('admin/gateways.provider_label') }}"
            helper="{{ __('admin/gateways.provider_helper') }}"
            required
            disabled
        >
            <option selected>{{ $gateway->provider }}</option>
        </x-admin::select>
        <x-admin::toggle
            name="instance_active"
            label="{{ __('admin/gateways.is_active_label') }}"
            helper="{{ __('admin/gateways.is_active_helper') }}"
            :checked="(bool)old('instance_active', $gateway->is_active)"
        />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        @foreach($schema as $key => $field)
            @if(in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                <x-admin::input 
                    name="configurations[{{ $gateway->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    type="{{ $field['type'] }}"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                    value="{{ old('configurations.'.$gateway->provider.'.'.$key, $gateway->config[$key] ?? $field['default'] ?? '') }}" 
                />
            @elseif($field['type'] === 'textarea')
                <x-admin::textarea
                    name="configurations[{{ $gateway->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                >{{ old('configurations.'.$gateway->provider.'.'.$key, $gateway->config[$key] ?? $field['default'] ?? '') }}</x-admin::textarea>
            @elseif($field['type'] === 'toggle')
                <x-admin::toggle
                    name="configurations[{{ $gateway->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :checked="(bool)old('configurations.'.$gateway->provider.'.'.$key, $gateway->config[$key] ?? $field['default'] ?? false)"
                />
            @elseif($field['type'] === 'select')
                <x-admin::select
                    name="configurations[{{ $gateway->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                >
                    @foreach($field['options'] ?? [] as $optValue => $optLabel)
                        <option 
                            value="{{ $optValue }}" 
                            {{ old('configurations.'.$gateway->provider.'.'.$key, $gateway->config[$key] ?? $field['default'] ?? '') == $optValue ? 'selected' : '' }}
                        >
                            {{ $optLabel }}
                        </option>
                    @endforeach
                </x-admin::select>
            @elseif($field['type'] === 'radio')
                <x-admin::radio.group 
                    name="configurations[{{ $gateway->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                >
                    @foreach($field['options'] as $optVal => $optLabel)
                        <x-admin::radio.option
                            name="configurations[{{ $gateway->provider }}][{{ $key }}]"
                            value="{{ $optVal }}"
                            label="{{ $optLabel }}"
                            :checked="old('configurations.'.$gateway->provider.'.'.$key, $gateway->config[$key] ?? $field['default'] ?? '') == $optVal" 
                        />
                    @endforeach
                </x-admin::radio.group>
            @elseif($field['type'] === 'checkbox')
                <x-admin::checkbox 
                    name="configurations[{{ $gateway->provider }}][{{ $key }}]"
                    label="{{ $field['label'] }}"
                    helper="{{ $field['helper'] ?? '' }}"
                    :options="$field['options']" 
                    :checked="(array) old('configurations.'.$gateway->provider.'.'.$key, $gateway->config[$key] ?? $field['default'] ?? [])"
                />
            @endif
        @endforeach
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.gateways') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    </div>
</form>
@endsection