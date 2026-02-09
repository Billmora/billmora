@extends('client::services.show')

@section('workspaces')
<div class="bg-white border-2 border-billmora-2 rounded-2xl overflow-hidden">
    <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
        <h3 class="font-semibold text-slate-600">{{ $pageSchema['title'] }}</h3>
    </div>
    <div class="p-6">
        @if(isset($pageSchema['description']))
            <div class="mb-6 p-4 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
                {{ $pageSchema['description'] }}
            </div>
        @endif
        <form action="{{ route('client.services.provisioning.process', ['service' => $service->id, 'slug' => $slug]) }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($pageSchema['fields'] ?? [] as $key => $field)
                    @if(in_array($field['type'] ?? 'text', ['text', 'email', 'url', 'number']))
                        <x-client::input 
                            name="{{ $key }}"
                            label="{{ $field['label'] ?? ucfirst($key) }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            type="{{ $field['type'] ?? 'text' }}"
                            :required="str_contains($field['rules'] ?? '', 'required')"
                            value="{{ old($key, $field['default'] ?? '') }}"
                        />
                    @elseif(($field['type'] ?? '') === 'password')
                        <x-client::input 
                            name="{{ $key }}"
                            label="{{ $field['label'] ?? ucfirst($key) }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            type="password"
                            :required="str_contains($field['rules'] ?? '', 'required')"
                        />
                    @elseif(($field['type'] ?? '') === 'textarea')
                        <x-client::textarea
                            name="{{ $key }}"
                            label="{{ $field['label'] ?? ucfirst($key) }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            :required="str_contains($field['rules'] ?? '', 'required')"
                        >{{ old($key, $field['default'] ?? '') }}</x-client::textarea>
                    @elseif(($field['type'] ?? '') === 'boolean')
                        <div class="flex items-center pt-6">
                            <x-client::toggle
                                name="{{ $key }}"
                                label="{{ $field['label'] ?? ucfirst($key) }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                checked="{{ old($key, $field['default'] ?? false) }}"
                            />
                        </div>
                    @elseif(($field['type'] ?? '') === 'select')
                        <x-client::select
                            name="{{ $key }}"
                            label="{{ $field['label'] ?? ucfirst($key) }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            :required="str_contains($field['rules'] ?? '', 'required')"
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
                    @endif
                @endforeach
            </div>
            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-6 py-2 text-white font-medium rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection