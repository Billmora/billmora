@extends('admin::layouts.app')

@section('title', 'Template Settings - Mail')

@section('body')
<div class="flex flex-col gap-5">
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
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
            [
                'route' => route('admin.settings.mail.broadcast'),
                'icon' => 'lucide-megaphone',
                'label' => __('admin/settings/mail.tabs.broadcast'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <div class="flex flex-col gap-4">
        <div class="w-full md:w-100">
            <form action="{{ route('admin.settings.mail.template') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary" />
                </div>
                <input type="text" name="searchTemplateMail" id="searchTemplateMail" placeholder="{{ __('admin/common.search') }}" value="{{ request('searchTemplateMail') }}" class="w-full px-6 py-3 pl-10 bg-white placeholder:text-gray-400 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-billmora-2">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.key') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.name') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.active') }}</th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-2 bg-white">
                            @foreach ($templates as $template)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $template->key }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $template->name }}</td>
                                @if ($template->active)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ __('common.active') }}</td>
                                @else
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ __('common.inactive') }}</td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                    @can('settings.mail.template.update')
                                        <a href="{{ route('admin.settings.mail.template.edit', $template->id) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover underline">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            {{ $templates->links('admin::layouts.partials.pagination') }}
        </div>
    </div>
</div>
@endsection
