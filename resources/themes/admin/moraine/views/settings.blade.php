@extends('admin::layouts.app')

@section('title', 'System Settings')

@section('body')
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @can('settings.general.view')
        <a href="{{ route('admin.settings.general.company') }}" class="flex gap-4 items-center bg-white p-4 border-2 border-billmora-2 hover:border-billmora-primary rounded-2xl transition ease-in-out duration-150">
            <div class="bg-billmora-primary p-2 rounded-full">
                <x-lucide-bolt class="w-auto h-10 text-white" />
            </div>
            <div>
            <h4 class="text-lg text-slate-700 font-semibold">{{ __('admin/settings/general.title') }}</h4> 
            <p class="text-slate-500">{{ __('admin/settings/general.description') }}</p>
            </div>
        </a>
    @endcan
    @can('settings.mail.view')
        <a href="{{ route('admin.settings.mail.mailer') }}" class="flex gap-4 items-center bg-white p-4 border-2 border-billmora-2 hover:border-billmora-primary rounded-2xl transition ease-in-out duration-150">
            <div class="bg-billmora-primary p-2 rounded-full">
                <x-lucide-mail class="w-auto h-10 text-white" />
            </div>
            <div>
            <h4 class="text-lg text-slate-700 font-semibold">{{ __('admin/settings/mail.title') }}</h4> 
            <p class="text-slate-500">{{ __('admin/settings/mail.description') }}</p>
            </div>
        </a>
    @endcan
    @can('settings.auth.view')
        <a href="{{ route('admin.settings.auth.user') }}" class="flex gap-4 items-center bg-white p-4 border-2 border-billmora-2 hover:border-billmora-primary rounded-2xl transition ease-in-out duration-150">
            <div class="bg-billmora-primary p-2 rounded-full">
                <x-lucide-user-cog class="w-auto h-10 text-white" />
            </div>
            <div>
            <h4 class="text-lg text-slate-700 font-semibold">{{ __('admin/settings/auth.title') }}</h4> 
            <p class="text-slate-500">{{ __('admin/settings/auth.description') }}</p>
            </div>
        </a>
    @endcan
    @can('settings.captcha.view')
        <a href="{{ route('admin.settings.captcha.provider') }}" class="flex gap-4 items-center bg-white p-4 border-2 border-billmora-2 hover:border-billmora-primary rounded-2xl transition ease-in-out duration-150">
            <div class="bg-billmora-primary p-2 rounded-full">
                <x-lucide-shield class="w-auto h-10 text-white" />
            </div>
            <div>
            <h4 class="text-lg text-slate-700 font-semibold">{{ __('admin/settings/captcha.title') }}</h4> 
            <p class="text-slate-500">{{ __('admin/settings/captcha.description') }}</p>
            </div>
        </a>
    @endcan
    <a href="{{ route('admin.settings.roles') }}" class="flex gap-4 items-center bg-white p-4 border-2 border-billmora-2 hover:border-billmora-primary rounded-2xl transition ease-in-out duration-150">
        <div class="bg-billmora-primary p-2 rounded-full">
            <x-lucide-shield class="w-auto h-10 text-white" />
        </div>
        <div>
           <h4 class="text-lg text-slate-700 font-semibold">{{ __('admin/settings/role.title') }}</h4> 
           <p class="text-slate-500">{{ __('admin/settings/role.description') }}</p>
        </div>
    </a>
</div>
@endsection