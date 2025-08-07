@extends('admin::layouts.app')

@section('body')
<div class="flex flex-col gap-4">
    <div class="flex gap-4 bg-white w-full p-4 border-2 border-billmora-2 rounded-2xl">
        <a href="{{ route('admin.settings.general') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-billmora-primary text-slate-700 hover:text-white rounded-lg transition ease-in-out duration-150">
            <x-lucide-building class="w-auto h-5" />
            <span>{{ __('admin/settings/general.tabs.company') }}</span>
        </a>
    </div>
</div>
@endsection