@extends('admin::layouts.app')

@section('title', 'Company Settings - General')

@section('body')
<form action="{{ route('admin.settings.general.misc.update') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PATCH')
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.general.company'),
                'icon' => 'lucide-building',
                'label' => __('admin/settings/general.tabs.company'),
            ],
            [
                'route' => route('admin.settings.general.ordering'),
                'icon' => 'lucide-truck',
                'label' => __('admin/settings/general.tabs.ordering'),
            ],
            [
                'route' => route('admin.settings.general.invoice'),
                'icon' => 'lucide-file',
                'label' => __('admin/settings/general.tabs.invoice'),
            ],
            [
                'route' => route('admin.settings.general.credit'),
                'icon' => 'lucide-badge-cent',
                'label' => __('admin/settings/general.tabs.credit'),
            ],
            [
                'route' => route('admin.settings.general.term'),
                'icon' => 'lucide-badge-check',
                'label' => __('admin/settings/general.tabs.term'),
            ],
            [
                'route' => route('admin.settings.general.social'),
                'icon' => 'lucide-circle-fading-plus',
                'label' => __('admin/settings/general.tabs.social'),
            ],
            [
                'route' => route('admin.settings.general.misc'),
                'icon' => 'lucide-wrench',
                'label' => __('admin/settings/general.tabs.misc'),
            ],
        ]" 
        active="{{ request()->url() }}" />
    <div class="grid md:grid-cols-2 gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            type="number"
            name="misc_admin_pagination"
            min="1"
            label="{{ __('admin/settings/general.misc_admin_pagination_label') }}"
            helper="{{ __('admin/settings/general.misc_admin_pagination_helper') }}"
            value="{{ Billmora::getGeneral('misc_admin_pagination') }}" 
            required 
        />
        <x-admin::input 
            type="number"
            name="misc_client_pagination"
            min="1"
            label="{{ __('admin/settings/general.misc_client_pagination_label') }}"
            helper="{{ __('admin/settings/general.misc_client_pagination_helper') }}"
            value="{{ Billmora::getGeneral('misc_client_pagination') }}" 
            required 
        />
        <x-admin::toggle 
            name="misc_debug"
            label="{{ __('admin/settings/general.misc_debug_label') }}"
            helper="{{ __('admin/settings/general.misc_debug_helper') }}"
            :checked="config('app.debug')" 
        />
    </div>
    @can('settings.general.update')
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection
