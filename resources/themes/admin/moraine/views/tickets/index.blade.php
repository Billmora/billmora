@extends('admin::layouts.app')

@section('title', 'Manage Tickets')

@section('body')
<div class="flex flex-col gap-4">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-auto flex gap-2 flex-1 max-w-150">
            <form action="{{ route('admin.tickets') }}" method="GET" class="relative inline-block w-full group m-0">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                </div>
                @foreach(request()->only(['sort', 'direction']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                @foreach(request()->query() as $key => $value)
                    @if(str_starts_with($key, 'filter_') && !is_null($value) && $value !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <input type="text" name="search" id="search" placeholder="{{ __('admin/common.search') }}" value="{{ request('search') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-neutral-100 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
            
            <x-admin::drawer.trigger drawer="ticketsFilter" type="button" class="relative flex items-center justify-center bg-white border-2 border-billmora-neutral-100 hover:border-billmora-primary-500 text-slate-600 px-3 py-2 rounded-xl transition duration-300 cursor-pointer shrink-0">
                <x-lucide-filter class="w-5 h-auto" />
                @if(collect($filters ?? [])->filter()->isNotEmpty())
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                @endif
            </x-admin::drawer.trigger>
        </div>
        @can('tickets.create')
            <a href="{{ route('admin.tickets.create') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-plus class="w-auto h-5" />
                {{ __('common.create') }}
            </a>
        @endcan
    </div>

    {{-- Active Filters --}}
    @if(collect($filters ?? [])->filter()->isNotEmpty())
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm text-slate-500 font-medium mr-1">{{ __('common.filter') ?? 'Filters' }}:</span>
            
            @if(!empty($filters['ticket_number']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_ticket_number') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('admin/tickets.ticket_number_label') }}: {{ $filters['ticket_number'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif

            @if(!empty($filters['status']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_status') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('common.status') }}: {{ ucwords(str_replace('_', ' ', $filters['status'])) }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            @if(!empty($filters['priority']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_priority') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('admin/tickets.ticket_priority_label') }}: {{ ucfirst($filters['priority']) }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            @if(!empty($filters['department']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_department') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('admin/tickets.ticket_department_label') }}: {{ ucfirst($filters['department']) }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            @if(!empty($filters['date_from']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_date_from') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('common.created_at') }} ({{ __('common.date_from') ?? 'From' }}): {{ $filters['date_from'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            @if(!empty($filters['date_to']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_date_to') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('common.created_at') }} ({{ __('common.date_to') ?? 'To' }}): {{ $filters['date_to'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            @if(!empty($filters['last_reply_at_from']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_last_reply_at_from') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('admin/tickets.filter.last_reply_at_label') }} ({{ __('common.date_from') ?? 'From' }}): {{ $filters['last_reply_at_from'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            @if(!empty($filters['last_reply_at_to']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_last_reply_at_to') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('admin/tickets.filter.last_reply_at_label') }} ({{ __('common.date_to') ?? 'To' }}): {{ $filters['last_reply_at_to'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            <a href="{{ request()->url() }}" class="text-sm font-medium text-red-500 hover:text-red-600 ml-2">{{ __('common.clear_all') ?? 'Clear All' }}</a>
        </div>
    @endif
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-neutral-100 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-neutral-100">
                    <thead class="bg-billmora-neutral-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tickets.ticket_number_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tickets.ticket_subject_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tickets.ticket_status_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tickets.ticket_priority_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tickets.ticket_department_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/tickets.ticket_user_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-neutral-100 bg-white">
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    <a href="{{ route('admin.tickets.reply', ['ticket' => $ticket->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">{{ $ticket->ticket_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $ticket->subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ ucwords($ticket->priority) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ ucwords($ticket->department) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $ticket->user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    @can('tickets.reply')
                                        <a href="{{ route('admin.tickets.reply', ['ticket' => $ticket->id]) }}" class="inline-flex items-center text-sm font-semibold text-yellow-500 hover:text-yellow-600">
                                            {{ __('common.manage') }}
                                        </a>                               
                                    @endcan
                                    @can('tickets.update')
                                        <a href="{{ route('admin.tickets.edit', ['ticket' => $ticket->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">
                                            {{ __('common.edit') }}
                                        </a>                               
                                    @endcan
                                    @can('tickets.delete')
                                        <x-admin::modal.trigger modal="deleteModal-{{ $ticket->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">
                                            {{ __('common.delete') }}
                                        </x-admin::modal.trigger>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-400">{{ __('common.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        {{ $tickets->links('admin::layouts.partials.pagination') }}
    </div>
    @can('tickets.delete')
        @foreach ($tickets as $ticket)
            <x-admin::modal.content
                modal="deleteModal-{{ $ticket->id }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.delete_modal_title') }}"
                description="{{ __('common.delete_modal_description', ['item' => $ticket->ticket_number]) }}">
                <form action="{{ route('admin.tickets.destroy', ['ticket' => $ticket->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-neutral-50 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.cancel') }}
                        </x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.delete') }}
                        </button>
                    </div>
                </form>
            </x-admin::modal.content>
        @endforeach
    @endcan
</div>

<x-admin::drawer.content drawer="ticketsFilter" title="{{ __('common.filter_title', ['resource' => __('admin/navigation.tickets')]) }}" action="{{ request()->url() }}">
    @if(request()->has('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
    @endif

    <x-admin::select name="filter_status" label="{{ __('admin/tickets.filter.status_label') }}">
        <option value="open" @selected(request('filter_status') === 'open')>{{ __('admin/tickets.filter.status_open') }}</option>
        <option value="answered" @selected(request('filter_status') === 'answered')>{{ __('admin/tickets.filter.status_answered') }}</option>
        <option value="replied" @selected(request('filter_status') === 'replied')>{{ __('admin/tickets.filter.status_replied') }}</option>
        <option value="in_progress" @selected(request('filter_status') === 'in_progress')>{{ __('admin/tickets.filter.status_in_progress') }}</option>
        <option value="on_hold" @selected(request('filter_status') === 'on_hold')>{{ __('admin/tickets.filter.status_on_hold') }}</option>
        <option value="closed" @selected(request('filter_status') === 'closed')>{{ __('admin/tickets.filter.status_closed') }}</option>
    </x-admin::select>

    <x-admin::select name="filter_priority" label="{{ __('admin/tickets.filter.priority_label') }}">
        <option value="low" @selected(request('filter_priority') === 'low')>{{ __('admin/tickets.filter.priority_low') }}</option>
        <option value="normal" @selected(request('filter_priority') === 'normal')>{{ __('admin/tickets.filter.priority_normal') }}</option>
        <option value="medium" @selected(request('filter_priority') === 'medium')>{{ __('admin/tickets.filter.priority_medium') }}</option>
        <option value="high" @selected(request('filter_priority') === 'high')>{{ __('admin/tickets.filter.priority_high') }}</option>
    </x-admin::select>

    <x-admin::select name="filter_department" label="{{ __('admin/tickets.filter.department_label') }}">
        @foreach(Billmora::getTicket('ticketing_departments') as $department)
            <option value="{{ $department }}" @selected(request('filter_department') === $department)>{{ ucfirst($department) }}</option>
        @endforeach
    </x-admin::select>

    <div class="grid grid-cols-2 gap-4">
        <x-admin::input name="filter_date_from" type="date"
            label="{{ __('common.created_at') }} ({{ __('common.date_from') }})"
            value="{{ request('filter_date_from') }}" />
        <x-admin::input name="filter_date_to" type="date"
            label="{{ __('common.created_at') }} ({{ __('common.date_to') }})"
            value="{{ request('filter_date_to') }}" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-admin::input name="filter_last_reply_at_from" type="date"
            label="{{ __('admin/tickets.filter.last_reply_at_label') }} ({{ __('common.date_from') }})"
            value="{{ request('filter_last_reply_at_from') }}" />
        <x-admin::input name="filter_last_reply_at_to" type="date"
            label="{{ __('admin/tickets.filter.last_reply_at_label') }} ({{ __('common.date_to') }})"
            value="{{ request('filter_last_reply_at_to') }}" />
    </div>
</x-admin::drawer.content>

@endsection