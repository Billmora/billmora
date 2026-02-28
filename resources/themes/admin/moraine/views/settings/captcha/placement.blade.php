@extends('admin::layouts.app')

@section('title', 'Placement Settings - Captcha')

@section('body')
<form action="{{ route('admin.settings.captcha.placement.update') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PATCH')
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.captcha.provider'),
                'icon' => 'lucide-earth-lock',
                'label' => __('admin/settings/captcha.tabs.provider'),
            ],
            [
                'route' => route('admin.settings.captcha.placement'),
                'icon' => 'lucide-waypoints',
                'label' => __('admin/settings/captcha.tabs.placement'),
            ],
        ]" 
        active="{{ request()->url() }}" />
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::multiselect
            name="placements_enabled_forms"
            :options="collect($forms)->map(fn($form) => [
                'value' => $form,
                'title' => ucwords(str_replace('_', ' ', $form)),
            ])->toArray()"
            :selected="old('placements_enabled_forms', Billmora::getCaptcha('placements_enabled_forms'))"
            label="{{ __('admin/settings/captcha.placements_enabled_forms_label') }}"
            helper="{{ __('admin/settings/captcha.placements_enabled_forms_helper') }}"
        />
    </div>
    @can('settings.captcha.update')
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    @endcan
</form>
@endsection