@extends('admin::layouts.app')

@section('title', 'Variant Option')

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.variants.edit', ['id' => $variant->id]),
                'icon' => 'lucide-boxes',
                'label' => __('admin/variants.tabs.summary'),
            ],
            [
                'route' => route('admin.variants.options', ['id' => $variant->id]),
                'icon' => 'lucide-codesandbox',
                'label' => __('admin/variants.tabs.options'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
</div>
@endsection