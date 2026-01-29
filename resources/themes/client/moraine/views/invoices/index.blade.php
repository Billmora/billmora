@extends('client::layouts.app')

@section('title', 'Invoices')

@section('body')
<div class="overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
            <table class="min-w-full divide-y divide-billmora-2">
                <thead class="bg-billmora-2">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/invoice.invoice_number') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/invoice.invoice_date') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/invoice.due_date') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/invoice.total') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
                        <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-billmora-2 bg-white">
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                <a href="{{ route('client.invoices.show', ['invoice' => $invoice->invoice_number]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $invoice->created_at->toDateString() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $invoice->due_date->toDateString() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ Currency::format($invoice->total, $invoice->currency) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $invoice->status }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                <a href="{{ route('client.invoices.show', ['invoice' => $invoice->invoice_number]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">{{ __('client/invoice.manage') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div>
    {{ $invoices->links('client::layouts.partials.pagination') }}
</div>
@endsection