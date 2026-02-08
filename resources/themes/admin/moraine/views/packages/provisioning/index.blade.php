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
                'label' => 'Provisioning',
            ],
        ]" 
        active="{{ request()->url() }}"
    />
    <form action="{{ route('admin.packages.provisioning.update', ['id' => $package->id]) }}" method="POST" class="grid gap-6"
        x-data="{
            currentDriver: '{{ $selectedDriver ?? 'none' }}',
            currentInstance: '{{ $selectedInstanceId }}',
            baseUrl: '{{ route('admin.packages.provisioning', $package->id) }}',
            
            init() {
                this.$watch('currentDriver', (value) => {
                    this.currentInstance = ''; 
                    this.refreshPage();
                });
                
                this.$watch('currentInstance', (value) => {
                    if (this.currentDriver !== 'none' && value) {
                        this.refreshPage();
                    }
                });
            },

            refreshPage() {
                let url = this.baseUrl + '?driver=' + this.currentDriver;
                
                if (this.currentDriver !== 'none' && this.currentInstance) {
                    url += '&instance=' + this.currentInstance;
                }
                
                window.location.href = url;
            }
        }"
    >
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white rounded-lg shadow p-6">
            <x-admin::select
                name="provisioning_driver"
                label="{{ __('admin/packages.provisioning.driver_label') }}"
                helper="{{ __('admin/packages.provisioning.driver_helper') }}"
                x-model="currentDriver"
                required
            >
                <option value="none">None (No Provisioning)</option>
                @foreach($drivers as $driverName)
                    <option value="{{ $driverName }}">{{ $driverName }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::select
                name="instance_reference"
                label="{{ __('admin/packages.provisioning.instance_label') }}"
                helper="{{ __('admin/packages.provisioning.instance_helper') }}"
                x-model="currentInstance"
                :disabled="empty($selectedDriver) || $instances->isEmpty()"
                required
            >
                @foreach($instances as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </x-admin::select>
        </div>
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            @if(empty($formFields))
                <div class="text-center text-gray-400 py-10">
                    {{ __('admin/packages.provisioning.unavailable_configuration') }}
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($formFields as $key => $field)
                        @if(in_array($field['type'], ['text', 'email', 'url', 'number']))
                            <x-admin::input 
                                name="config[{{ $key }}]"
                                label="{{ $field['label'] ?? ucfirst($key) }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                type="{{ $field['type'] }}"
                                :required="str_contains($field['rules'] ?? '', 'required')"
                                value="{{ old('config.'.$key, $package->provisioning_config[$key] ?? $field['default'] ?? '') }}"
                            />
                        @elseif($field['type'] === 'password')
                            <x-admin::input 
                                name="config[{{ $key }}]"
                                label="{{ $field['label'] ?? ucfirst($key) }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                type="password"
                                value="" 
                            />
                        @elseif($field['type'] === 'boolean')
                            <div class="flex items-center pt-6">
                                <x-admin::toggle
                                    name="config[{{ $key }}]"
                                    label="{{ $field['label'] ?? ucfirst($key) }}"
                                    helper="{{ $field['helper'] ?? '' }}"
                                    value="1"
                                    :checked="(bool)old('config.'.$key, $package->provisioning_config[$key] ?? $field['default'] ?? false)"
                                />
                            </div>
                        @elseif($field['type'] === 'select')
                            <x-admin::select
                                name="config[{{ $key }}]"
                                label="{{ $field['label'] ?? ucfirst($key) }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                :required="str_contains($field['rules'] ?? '', 'required')"
                            >
                                @foreach($field['options'] ?? [] as $optValue => $optLabel)
                                    <option 
                                        value="{{ $optValue }}" 
                                        {{ (string)old('config.'.$key, $package->provisioning_config[$key] ?? $field['default'] ?? '') === (string)$optValue ? 'selected' : '' }}
                                    >
                                        {{ $optLabel }}
                                    </option>
                                @endforeach
                            </x-admin::select>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.packages.pricing', ['id' => $package->id]) }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.save') }}</button>
        </div>
    </form>
</div>
@endsection