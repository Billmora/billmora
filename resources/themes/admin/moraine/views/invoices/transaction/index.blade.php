@extends('admin::layouts.app')

@section('title', "Invoice Transasctions - {$invoice->invoice_number}")

@section('body')
<div class="flex flex-col gap-4">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.invoices.edit', ['invoice' => $invoice->invoice_number]),
                'icon' => 'lucide-receipt-text',
                'label' => __('admin/invoices.tabs.summary'),
            ],
            [
                'route' => route('admin.invoices.transaction', ['invoice' => $invoice->invoice_number]),
                'icon' => 'lucide-landmark',
                'label' => __('admin/invoices.tabs.transaction'),
            ],
        ]" 
        active="{{ request()->url() }}"
    />
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-100">
            <form action="{{ route('admin.transactions') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary" />
                </div>
                <input type="text" name="search" id="search" placeholder="{{ __('admin/common.search') }}" value="{{ request('search') }}" class="w-full px-6 py-3 pl-10 bg-white placeholder:text-gray-400 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
        </div>
        @can('transactions.create')
            <a href="{{ route('admin.invoices.transaction.create', ['invoice' => $invoice->invoice_number]) }}" class="flex gap-1 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-plus class="w-auto h-5" />
                {{ __('common.create') }}
            </a>
        @endcan
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/transactions.reference_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/transactions.gateway_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/transactions.description_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/transactions.amount_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/transactions.fee_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.created_at') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $transaction->reference }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $transaction->plugin?->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $transaction->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ Currency::format($transaction->amount, $transaction->currency) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ Currency::format($transaction->fee, $transaction->currency) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $transaction->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    @can('transactions.delete')
                                        <x-admin::modal.trigger modal="deleteModal-{{ $transaction->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
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
        {{ $transactions->links('admin::layouts.partials.pagination') }}
    </div>
    @can('transactions.delete')
        @foreach ($transactions as $transaction)
            <x-admin::modal.content
                modal="deleteModal-{{ $transaction->id }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.delete_modal_title') }}"
                description="{{ __('common.delete_modal_description', ['item' => $transaction->reference ?? $transaction->id]) }}">
                <form action="{{ route('admin.invoices.transaction.destroy', ['invoice' => $invoice->invoice_number, 'transaction' => $transaction->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                    </div>
                </form>
            </x-admin::modal.content>
        @endforeach
    @endcan
</div>
@endsection