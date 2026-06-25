@extends('admin::layouts.app')

@section('title', "Package Fields")

@section('body')
    <div class="flex flex-col gap-5">
        <x-admin::tabs :tabs="[
            [
                'route' => route('admin.packages.edit', ['package' => $package->id]),
                'icon' => 'lucide-package',
                'label' => __('admin/packages.tabs.summary'),
            ],
            [
                'route' => route('admin.packages.pricing', ['package' => $package->id]),
                'icon' => 'lucide-badge-cent',
                'label' => __('admin/packages.tabs.pricing'),
            ],
            [
                'route' => route('admin.packages.fields', ['package' => $package->id]),
                'icon' => 'lucide-list-todo',
                'label' => __('admin/packages.tabs.fields'),
            ],
            [
                'route' => route('admin.packages.provisioning', ['package' => $package->id]),
                'icon' => 'lucide-plug',
                'label' => 'Provisioning',
            ],
            [
                'route' => route('admin.packages.scaling', ['package' => $package->id]),
                'icon' => 'lucide-arrow-up-down',
                'label' => __('admin/packages.tabs.scaling'),
            ],
        ]" active="{{ request()->url() }}" />
        
        @livewire('admin.packages.package-fields', ['package' => $package])
    </div>
@endsection
