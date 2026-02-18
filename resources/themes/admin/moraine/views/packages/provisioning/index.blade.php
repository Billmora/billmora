@extends('admin::layouts.app')

@section('title', "Package Provisioning")

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.packages.edit', ['id' => $package->id]),
                'icon' => 'lucide-package',
                'label' => __('admin/packages.tabs.summary'),
            ],
            [
                'route' => route('admin.packages.pricing', ['id' => $package->id]),
                'icon' => 'lucide-badge-cent',
                'label' => __('admin/packages.tabs.pricing'),
            ],
            [
                'route' => route('admin.packages.provisioning', ['id' => $package->id]),
                'icon' => 'lucide-plug',
                'label' => __('admin/packages.tabs.provisioning'),
            ],
        ]" 
        active="{{ request()->url() }}"
    />
    <form 
        action="{{ route('admin.packages.provisioning.update', ['id' => $package->id]) }}" 
        method="POST" 
        class="grid gap-6"
        x-data="{
            currentId: '{{ $selectedId ?? '' }}',
            baseUrl: '{{ route('admin.packages.provisioning', ['id' => $package->id]) }}',
            
            init() {
                this.$watch('currentId', (value) => {
                    if (value !== '{{ $selectedId ?? '' }}') {
                        window.location.href = this.baseUrl + '?instance_id=' + value;
                    }
                });
            }
        }"
    >
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
            <x-admin::select
                name="provisioning_id"
                label="{{ __('admin/packages.provisioning.instance_label') }}"
                helper="{{ __('admin/packages.provisioning.instance_helper') }}"
                x-model="currentId"
                required
            >
                <option value="">None (No Provisioning)</option>
                @foreach($provisionings as $plugin)
                    <option value="{{ $plugin->id }}">
                        {{ $plugin->name }} ({{ $plugin->provider }})
                    </option>
                @endforeach
            </x-admin::select>
        </div>
        @if($selectedId && !empty($schema))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
                @foreach($schema as $key => $field)
                    @if(in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                        <x-admin::input 
                            name="provisioning_config[{{ $key }}]"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            type="{{ $field['type'] }}"
                            :required="str_contains($field['rules'] ?? '', 'required')"
                            value="{{ old('provisioning_config.'.$key, $package->provisioning_config[$key] ?? $field['default'] ?? '') }}"
                        />
                    @elseif($field['type'] === 'textarea')
                        <x-admin::textarea
                            name="provisioning_config[{{ $key }}]"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            :required="str_contains($field['rules'] ?? '', 'required')"
                        >{{ old('provisioning_config.'.$key, $package->provisioning_config[$key] ?? $field['default'] ?? '') }}</x-admin::textarea>
                    @elseif($field['type'] === 'select')
                        <x-admin::select
                            name="provisioning_config[{{ $key }}]"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            :required="str_contains($field['rules'] ?? '', 'required')"
                        >
                            @foreach($field['options'] ?? [] as $optValue => $optLabel)
                                <option 
                                    value="{{ $optValue }}" 
                                    {{ old('provisioning_config.'.$key, $package->provisioning_config[$key] ?? $field['default'] ?? '') == $optValue ? 'selected' : '' }}
                                >
                                    {{ $optLabel }}
                                </option>
                            @endforeach
                        </x-admin::select>
                    @elseif($field['type'] === 'toggle')
                        <div class="flex items-center pt-6">
                            <x-admin::toggle
                                name="provisioning_config[{{ $key }}]"
                                label="{{ $field['label'] }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                :checked="(bool)old('provisioning_config.'.$key, $package->provisioning_config[$key] ?? $field['default'] ?? false)"
                            />
                        </div>
                    @elseif($field['type'] === 'radio')
                        <x-admin::radio.group 
                            name="provisioning_config[{{ $key }}]"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            :required="str_contains($field['rules'] ?? '', 'required')"
                        >
                            @foreach($field['options'] as $optVal => $optLabel)
                                <x-admin::radio.option
                                    name="provisioning_config[{{ $key }}]"
                                    value="{{ $optVal }}"
                                    label="{{ $optLabel }}"
                                    :checked="old('provisioning_config.'.$key, $package->provisioning_config[$key] ?? $field['default'] ?? '') == $optVal"
                                />
                            @endforeach
                        </x-admin::radio.group>
                    @elseif($field['type'] === 'checkbox')
                        <x-admin::checkbox 
                            name="provisioning_config[{{ $key }}]"
                            label="{{ $field['label'] }}"
                            helper="{{ $field['helper'] ?? '' }}"
                            :options="$field['options']" 
                            :checked="(array) old('provisioning_config.'.$key, $package->provisioning_config[$key] ?? $field['default'] ?? [])"
                            :required="str_contains($field['rules'] ?? '', 'required')"
                        />
                    @endif
                @endforeach
            </div>
        @elseif($selectedId && empty($schema))
            <div class="bg-white text-slate-500 p-4 border-2 border-billmora-2 rounded-2xl">
                {{ __('admin/packages.provisioning.unavailable_schema') }}
            </div>
        @endif
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.packages') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.cancel') }}
            </a>
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.save') }}
            </button>
        </div>
    </form>
</div>
@endsection