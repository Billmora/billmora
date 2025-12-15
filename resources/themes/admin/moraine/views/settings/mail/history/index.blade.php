@extends('admin::layouts.app')

@section('title', 'History Audit - Mail')

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
            [
                'route' => route('admin.settings.mail.broadcast'),
                'icon' => 'lucide-megaphone',
                'label' => __('admin/settings/mail.tabs.broadcast'),
            ],
            [
                'route' => route('admin.settings.mail.history'),
                'icon' => 'lucide-mails',
                'label' => __('admin/settings/mail.tabs.history'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="w-full md:w-100">
                <form action="{{ route('admin.settings.mail.history') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                    <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                        <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary" />
                    </div>
                    <input type="text" name="searchHistoryMail" id="searchHistoryMail" placeholder="{{ __('admin/common.search') }}" value="{{ request('searchHistoryMail') }}" class="w-full px-6 py-3 pl-10 bg-white placeholder:text-gray-400 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary">
                    <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                    </div>
                </form>
            </div>
            <div class="flex gap-4 ml-auto">
                @can('audit.email.history.export')
                    <form action="{{ route('admin.settings.mail.history.export') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex gap-1 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            <x-lucide-file-down class="w-auto h-5" />
                            {{ __('common.export') }}
                        </button>
                    </form>
                @endcan
                @can('audit.email.history.delete')
                    <x-admin::modal.trigger modal="clearModalHistory" variant="open" class="flex gap-1 items-center bg-red-500 hover:bg-red-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        <x-lucide-eraser class="w-auto h-5" />
                        {{ __('common.clear') }}
                    </x-admin::modal.trigger>
                @endcan
            </div>
        </div>
        <div class="overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-billmora-2">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">#</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/audits/email.event_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/audits/email.to_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.created_at') }}</th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-2 bg-white">
                            @foreach ($histories as $history)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $histories->total() - $loop->index }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    <a href="{{ route('admin.settings.mail.history.show', ['id' => $history->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">{{ $history->event }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    @if ($history->user_id)
                                        <a href="{{ route('admin.users.summary', ['id' => $history->user_id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">
                                            {{ $history->to }}
                                        </a>
                                    @else
                                        {{ $history->to }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $history->status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $history->created_at }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.settings.mail.history.show', ['id' => $history->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">
                                        {{ __('common.view') }}
                                    </a>
                                    <a href="{{ route('admin.settings.mail.history.preview', ['id' => $history->id]) }}" class="inline-flex items-center text-sm font-semibold text-yellow-400 hover:text-yellow-500"
                                        x-data="{ windowWidth: 900, windowHeight: 600 }"
                                        x-on:click.prevent="
                                                const left = (screen.width/2) - (windowWidth/2);
                                                const top = (screen.height/2) - (windowHeight/2);
                                                window.open($event.target.href, 'preview', 
                                                        `width=${windowWidth},height=${windowHeight},top=${top},left=${left},resizable=yes,scrollbars=yes`);
                                        ">
                                        {{ __('common.preview') }}
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            {{ $histories->links('admin::layouts.partials.pagination') }}
        </div>
    </div>
    @can('audit.email.history.delete')
        <x-admin::modal.content
            modal="clearModalHistory"
            variant="danger"
            size="xl"
            position="centered"
            title="{{ __('common.clear_modal_title') }}"
            description="{{ __('common.clear_modal_description', ['item' => __('admin/audits/email.title')]) }}">
            <form action="{{ route('admin.settings.mail.history.clear') }}" method="POST">
                @csrf
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                    <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                </div>
            </form>
        </x-admin::modal.content>
    @endcan
</div>
@endsection
