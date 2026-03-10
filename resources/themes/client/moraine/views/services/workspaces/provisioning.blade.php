@extends('client::services.show')

@section('workspaces')
<div class="bg-white border-2 border-billmora-2 rounded-2xl overflow-hidden">
    <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
        <h3 class="font-semibold text-slate-600 flex items-center gap-2">
            @if(!empty($clientActions[$slug]['icon']))
                <i class="{{ $clientActions[$slug]['icon'] }}"></i>
            @endif
            {{ $clientActions[$slug]['label'] }}
        </h3>
    </div>
    <div class="p-6">
        <form action="{{ route('client.services.provisioning.handle', ['service' => $service->service_number, 'slug' => $slug]) }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($pageSchema as $key => $field)
                    @if(in_array($field['type'], ['text', 'email', 'url', 'number']))
                        <x-client::input 
                            name="{{ $key }}"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            type="{{ $field['type'] }}"
                            placeholder="{{ $field['placeholder'] ?? '' }}"
                            :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                            value="{{ old($key, $field['default'] ?? '') }}"
                        />
                    @elseif(($field['type']) === 'password')
                        <x-client::input 
                            name="{{ $key }}"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            type="password"
                            placeholder="{{ $field['placeholder'] ?? '' }}"
                            :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                        />
                    @elseif(($field['type']) === 'toggle')
                        <div class="flex items-center pt-6">
                            <x-client::toggle
                                name="{{ $key }}"
                                label="{{ $field['label'] }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                :checked="(bool)old($key, $field['default'] ?? false)"
                            />
                        </div>
                    @elseif(($field['type']) === 'select')
                        <x-client::select
                            name="{{ $key }}"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                        >
                            @foreach($field['options'] ?? [] as $optValue => $optLabel)
                                <option 
                                    value="{{ $optValue }}" 
                                    {{ (string)old($key, $field['default'] ?? '') === (string)$optValue ? 'selected' : '' }}
                                >
                                    {{ $optLabel }}
                                </option>
                            @endforeach
                        </x-client::select>
                    @elseif(($field['type']) === 'textarea')
                        <x-client::textarea
                            name="{{ $key }}"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            placeholder="{{ $field['placeholder'] ?? '' }}"
                            :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                        >{{ old($key, $field['default'] ?? '') }}</x-client::textarea>
                    @elseif(($field['type']) === 'radio')
                        <x-client::radio.group 
                            name="{{ $key }}"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                        >
                            @foreach($field['options'] as $optVal => $optLabel)
                                <x-client::radio.option
                                    name="{{ $key }}"
                                    value="{{ $optVal }}"
                                    label="{{ $optLabel }}"
                                    :checked="old($key, $field['default'] ?? '') == $optVal"
                                />
                            @endforeach
                        </x-client::radio.group>
                    @elseif(($field['type']) === 'checkbox')
                        <x-client::checkbox 
                            name="{{ $key }}[]"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            :options="$field['options']" 
                            :checked="(array) old($key, $field['default'] ?? [])"
                        />
                    @endif
                @endforeach
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-6 py-2 text-white font-medium rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection