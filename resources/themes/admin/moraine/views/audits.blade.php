@extends('admin::layouts.app')

@section('title', 'System Settings')

@section('body')
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @can('audit.email.history.view')
        <a href="{{ route('admin.settings.mail.history') }}" class="flex gap-4 items-center bg-white p-4 border-2 border-billmora-2 hover:border-billmora-primary rounded-2xl transition ease-in-out duration-150">
            <div class="bg-billmora-primary p-2 rounded-full">
                <x-lucide-mails class="w-auto h-10 text-white" />
            </div>
            <div>
            <h4 class="text-lg text-slate-700 font-semibold">{{ __('admin/audits/email.title') }}</h4> 
            <p class="text-slate-500">{{ __('admin/audits/email.description') }}</p>
            </div>
        </a>
    @endcan
</div>
@endsection