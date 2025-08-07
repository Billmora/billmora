@extends('admin::layouts.app')

@section('body')
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    <a href="{{ route('admin.settings.general') }}" class="flex gap-4 items-center bg-white p-4 border-2 border-billmora-2 hover:border-billmora-primary rounded-2xl transition ease-in-out duration-150">
        <div class="bg-billmora-primary p-2 rounded-full">
            <x-lucide-bolt class="w-auto h-10 text-white" />
        </div>
        <div>
           <h4 class="text-lg text-slate-700 font-semibold">{{ __('admin/settings/general.title') }}</h4> 
           <p class="text-slate-500">{{ __('admin/settings/general.description') }}</p>
        </div>
    </a>
</div>
@endsection