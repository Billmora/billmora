@extends('admin::layouts.app')

@section('title', 'System Tasks Backlog')

@section('body')
<div class="flex flex-col gap-4">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-100">
            <form action="{{ route('admin.tasks') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                </div>
                <input type="text" name="search" id="search" placeholder="{{ __('admin/common.search') }}" value="{{ request('search') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tasks.date_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tasks.event_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tasks.target_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tasks.error_message_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @forelse($failedTasks as $task)
                            @php
                                $props = $task->properties ?? [];
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $task->created_at->format(Billmora::getGeneral('company_date_format') . ' H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $task->event }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    @if(isset($props['service_id']))
                                        <a href="{{ route('admin.services.edit', ['service' => $props['service_id']]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">
                                            Service #{{ $props['service_id'] }}
                                        </a>
                                    @else
                                        {{ __('admin/tasks.target_system') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ Str::limit($props['message'] ?? __('admin/tasks.unknown_error'), 100) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.audits.system.show', ['id' => $task->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">
                                        {{ __('common.view') }}
                                    </a>
                                    @can('tasks.retry')
                                        <x-admin::modal.trigger modal="retryModal-{{ $task->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-yellow-500 hover:text-yellow-600 cursor-pointer">
                                            {{ __('admin/tasks.retry') }}
                                        </x-admin::modal.trigger>
                                    @endcan
                                    @can('tasks.dismiss')
                                        <x-admin::modal.trigger modal="dismissModal-{{ $task->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">
                                            {{ __('admin/tasks.dismiss') }}
                                        </x-admin::modal.trigger>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-slate-500">{{ __('admin/tasks.empty_state') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        {{ $failedTasks->links('admin::layouts.partials.pagination') }}
    </div>
    @foreach ($failedTasks as $task)
        @can('tasks.retry')
            <x-admin::modal.content
                modal="retryModal-{{ $task->id }}"
                variant="warning"
                size="xl"
                position="centered"
                title="{{ __('common.confirm_modal_title') }}"
                description="{{ __('common.confirm_modal_description', ['item' => $task->event]) }}"
            >
                <form action="{{ route('admin.tasks.retry', ['task' => $task->id]) }}" method="POST">
                    @csrf
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.cancel') }}
                        </x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.submit') }}
                        </button>
                    </div>
                </form>
            </x-admin::modal.content>
        @endcan
        @can('tasks.dismiss')
            <x-admin::modal.content
                modal="dismissModal-{{ $task->id }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.confirm_modal_title') }}"
                description="{{ __('common.confirm_modal_description', ['item' => $task->event]) }}"
            >
                <form action="{{ route('admin.tasks.dismiss', ['task' => $task->id]) }}" method="POST">
                    @csrf
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.cancel') }}
                        </x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.submit') }}
                        </button>
                    </div>
                </form>
            </x-admin::modal.content>
        @endcan
    @endforeach
</div>
@endsection