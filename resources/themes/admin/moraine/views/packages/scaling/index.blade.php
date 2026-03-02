@extends('admin::layouts.app')

@section('title', "Package Scaling - {$package->name}")

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
            [
                'route' => route('admin.packages.scaling', ['package' => $package->id]),
                'icon' => 'lucide-arrow-up-down',
                'label' => __('admin/packages.tabs.scaling'),
            ],
        ]" 
        active="{{ request()->url() }}"
    />
    <form 
        action="{{ route('admin.packages.scaling.update', ['package' => $package->id]) }}" 
        method="POST" 
        class="grid gap-6"
    >
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
            <x-admin::multiselect
                name="scaling_target_packages"
                label="{{ __('admin/packages.scaling.target_packages_label') }}"
                helper="{{ __('admin/packages.scaling.target_packages_helper') }}"
                :options="$availablePackages->map(fn($package) => [
                    'value' => $package->id,
                    'title' => $package->name,
                    'subtitle' => $package->catalog->name,
                ])"
                :selected="old('scaling_target_packages', $selectedTargets)"
            />
        </div>
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