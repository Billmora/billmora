@extends('admin::layouts.app')

@section('title', "Activity Audit - {$user->email}")

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.users.summary', ['id' => $user->id]),
                'icon' => 'lucide-contact',
                'label' => __('admin/users.tabs.summary'),
            ],
            [
                'route' => route('admin.users.profile', ['id' => $user->id]),
                'icon' => 'lucide-user-pen',
                'label' => __('admin/users.tabs.profile'),
            ],
            [
                'route' => route('admin.users.activity', ['id' => $user->id]),
                'icon' => 'lucide-activity',
                'label' => __('admin/users.tabs.activity'),
            ],
        ]" 
        active="{{ request()->url() }}" />
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="w-full md:w-100">
                <form action="{{ route('admin.users.activity', ['id' => $user->id]) }}" method="GET" class="relative inline-block max-w-150 w-full group">
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
                @can('audit.user.activity.export')
                    <form action="{{ route('admin.users.activity.export', ['id' => $user->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="flex gap-1 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            <x-lucide-file-down class="w-auto h-5" />
                            {{ __('common.export') }}
                        </button>
                    </form>
                @endcan
                @can('audit.user.activity.delete')
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
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/audits/user.event_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.created_at') }}</th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-2 bg-white">
                            @foreach ($activities as $activity)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    <a href="{{ route('admin.users.activity.show', ['id' => $user->id, 'activity' => $activity->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">{{ $activity->event }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $activity->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.users.activity.show', ['id' => $user->id, 'activity' => $activity->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">
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
            {{ $activities->links('admin::layouts.partials.pagination') }}
        </div>
    </div>
    @can('audit.user.activity.delete')
        <x-admin::modal.content
            modal="clearModalHistory"
            variant="danger"
            size="xl"
            position="centered"
            title="{{ __('common.clear_modal_title') }}"
            description="{{ __('common.clear_modal_description', ['item' => __('admin/audits/user.title')]) }}">
            <form action="{{ route('admin.users.activity.clear', ['id' => $user->id]) }}" method="POST">
                @csrf
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                    <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.clear') }}</button>
                </div>
            </form>
        </x-admin::modal.content>
    @endcan
</div>
@endsection
