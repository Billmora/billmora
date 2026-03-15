@extends('client::layouts.app')

@section('title', 'Client Dashboard')

@section('body')
<div class="flex flex-col lg:flex-row gap-5">
    <div class="w-full lg:w-1/4 h-fit grid gap-6 items-center bg-billmora-bg p-8 text-center border-2 border-billmora-2 rounded-2xl">
        <img src="{{ $user->avatar }}?s=128" alt="user avatar" class="rounded-full w-32 h-auto mx-auto">
        <div class="flex flex-col">
            <span class="text-xl text-slate-600 font-bold break-all">{{ $user->fullname }}</span>
            <span class="text-md text-slate-500 font-semibold break-all">{{ $user->email }}</span>
        </div>
        <div class="grid gap-2 bg-billmora-primary-500 p-4 text-xs rounded-xl">
            <div class="flex gap-3 justify-between">
                <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.phone_number') }}</span>
                <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->phone_number }}</span>
            </div>
            <div class="flex gap-3 justify-between">
                <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.company_name') }}</span>
                <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->company_name }}</span>
            </div>
            <div class="flex gap-3 justify-between">
                <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.street_address_1') }}</span>
                <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->street_address_1 }}</span>
            </div>
            <div class="flex gap-3 justify-between">
                <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.street_address_2') }}</span>
                <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->street_address_2 }}</span>
            </div>
            <div class="flex gap-3 justify-between">
                <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.city') }}</span>
                <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->city }}</span>
            </div>
            <div class="flex gap-3 justify-between">
                <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.state') }}</span>
                <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->state }}</span>
            </div>
            <div class="flex gap-3 justify-between">
                <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.postcode') }}</span>
                <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->postcode }}</span>
            </div>
            <div class="flex gap-3 justify-between">
                <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.country') }}</span>
                <span class="text-slate-200 font-semibold text-end break-all">{{ config('utils.countries')[$user->billing->country] ?? $user->billing->country }}</span>
            </div>
        </div>
    </div>
    <div class="w-full lg:w-3/4 h-fit grid gap-5">
        <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-3 gap-5">
            <a href="{{ route('client.services') }}" class="flex items-center gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 hover:border-green-500 transition-colors rounded-2xl">
                <div class="bg-green-200 p-3 text-green-500 rounded-full">
                    <x-lucide-shopping-bag class="w-auto h-10" />
                </div>
                <div>
                    <h4 class="text-md font-semibold text-slate-500">{{ __('client/dashboard.active_services') }}</h4>
                    <span class="text-3xl font-bold text-slate-600">{{ $activeServicesCount }}</span>
                </div>
            </a>
            <a href="{{ route('client.invoices') }}" class="flex items-center gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 hover:border-red-500 transition-colors rounded-2xl">
                <div class="bg-red-200 p-3 text-red-500 rounded-full">
                    <x-lucide-receipt-text class="w-auto h-10" />
                </div>
                <div>
                    <h4 class="text-md font-semibold text-slate-500">{{ __('client/dashboard.unpaid_invoices') }}</h4>
                    <span class="text-3xl font-bold text-slate-600">{{ $unpaidInvoicesCount }}</span>
                </div>
            </a>
            <a href="{{ route('client.tickets') }}" class="md:col-span-full lg:col-span-1 flex items-center gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 hover:border-violet-500 transition-colors rounded-2xl">
                <div class="bg-violet-200 p-3 text-violet-500 rounded-full">
                    <x-lucide-ticket class="w-auto h-10" />
                </div>
                <div>
                    <h4 class="text-md font-semibold text-slate-500">{{ __('client/dashboard.open_tickets') }}</h4>
                    <span class="text-3xl font-bold text-slate-600">{{ $openTicketsCount }}</span>
                </div>
            </a>
        </div>
        <div class="overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                    <div class="flex justify-between bg-billmora-bg items-center p-4">
                        <h5 class="text-lg font-semibold text-slate-600">{{ __('client/dashboard.active_services') }}</h5>
                        <a href="{{ route('client.store') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('client/dashboard.order_new_services') }}
                        </a>
                    </div>
                    <table class="min-w-full divide-y divide-billmora-2">
                        <thead class="bg-billmora-2">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/dashboard.service_catalog') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/dashboard.service_package') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/dashboard.service_next_due_date') }}</th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-2 bg-billmora-bg">
                            @foreach ($activeServices as $service)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->package->catalog->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->next_due_date->format(Billmora::getGeneral('company_date_format')) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                        <a href="{{ route('client.services.show', ['service' => $service->service_number]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">{{ __('common.manage') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                        <div class="flex justify-between bg-billmora-bg items-center p-4">
                            <h5 class="text-lg font-semibold text-slate-600">{{ __('client/dashboard.unpaid_invoices') }}</h5>
                            <a href="{{ route('client.invoices') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                                {{ __('client/dashboard.view_all') }}
                            </a>
                        </div>
                        <table class="min-w-full divide-y divide-billmora-2">
                            <thead class="bg-billmora-2">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/dashboard.invoice_number') }}</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/dashboard.invoice_total') }}</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/dashboard.invoice_status') }}</th>
                                    <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-billmora-2 bg-billmora-bg">
                                @foreach ($unpaidInvoices as $invoice)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $invoice->invoice_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ Currency::format($invoice->total, $invoice->currency) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ ucwords($invoice->status) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                            <a href="{{ route('client.invoices.show', ['invoice' => $invoice->invoice_number]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">
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
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                        <div class="flex justify-between bg-billmora-bg items-center p-4">
                            <h5 class="text-lg font-semibold text-slate-600">{{ __('client/dashboard.open_tickets') }}</h5>
                            <a href="{{ route('client.tickets') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                                {{ __('client/dashboard.view_all') }}
                            </a>
                        </div>
                        <table class="min-w-full divide-y divide-billmora-2">
                            <thead class="bg-billmora-2">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/dashboard.ticket_number') }}</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/dashboard.ticket_subject') }}</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/dashboard.ticket_status') }}</th>
                                    <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-billmora-2 bg-billmora-bg">
                                @foreach ($openTickets as $ticket)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $ticket->ticket_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $ticket->subject }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ ucwords($ticket->status) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                            <a href="{{ route('client.tickets.reply', ['ticket' => $ticket->ticket_number]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">
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
        </div>
    </div>
</div>
@endsection