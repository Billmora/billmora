@extends('client::layouts.app')

@section('title', 'Tickets')

@section('body')
<div class="flex flex-col gap-4">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <a href="{{ route('client.tickets.create') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            <x-lucide-plus class="w-auto h-5" />
            {{ __('common.create') }}
        </a>
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/tickets.ticket_number_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/tickets.ticket_subject_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/tickets.ticket_status_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/tickets.ticket_priority_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/tickets.ticket_department_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    <a href="{{ route('client.tickets.reply', ['ticket' => $ticket->ticket_number]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">{{ $ticket->ticket_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $ticket->subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ ucwords($ticket->priority) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ ucwords($ticket->department) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    <a href="{{ route('client.tickets.reply', ['ticket' => $ticket->ticket_number]) }}" class="inline-flex items-center text-sm font-semibold text-yellow-500 hover:text-yellow-600">
                                        {{ __('common.manage') }}
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
        {{ $tickets->links('client::layouts.partials.pagination') }}
    </div>
</div>
@endsection