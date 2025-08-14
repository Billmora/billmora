@extends('admin::layouts.app')

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.mail.mailer'),
                'icon' => 'lucide-send',
                'label' => __('admin/settings/mail.tabs.mailer'),
            ],
            [
                'route' => route('admin.settings.mail.template'),
                'icon' => 'lucide-mailbox',
                'label' => __('admin/settings/mail.tabs.template'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        {{-- Code here --}}
    </div>
</div>
@endsection
