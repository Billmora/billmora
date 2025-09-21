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
        <div class="overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-billmora-2">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">#</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/mail.history_field.event') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/mail.history_field.to') }}</th>
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
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                    <a href="{{ route('admin.settings.mail.history.show', ['id' => $history->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">
                                        {{ __('common.view') }}
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
</div>
@endsection
