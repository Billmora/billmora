@extends('client::layouts.app')

@section('title', "Invoice #{$invoice->invoice_number}")

@section('body')
<div class="flex flex-col lg:flex-row gap-5">
    <div class="w-full lg:w-5/7 h-fit grid gap-6">
        <form action="{{ route('client.invoices.download', ['invoice' => $invoice->invoice_number]) }}" class="ml-auto">
            <button type="submit" class="flex gap-2 items-center bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-download class="w-5 h-auto" />
                {{ __('client/invoice.download_label') }}
            </button>
        </form>
        <div class="grid gap-8 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-0 items-center">
                <div class="col-span-8">
                    <span class="text-3xl font-semibold text-slate-600">{{ __('client/invoice.invoice_label', ['number' => $invoice->invoice_number]) }}</span>
                </div>
                <div class="grid gap-2 col-span-4">
                    <div class="grid grid-cols-2">
                        <span class="font-semibold text-slate-600">{{ __('client/invoice.invoice_date') }}</span>
                        <span class="font-semibold text-slate-500">{{ $invoice->created_at->format(Billmora::getGeneral('company_date_format')) }}</span>
                    </div>
                    <div class="grid grid-cols-2">
                        <span class="font-semibold text-slate-600">{{ __('client/invoice.due_date') }}</span>
                        <span class="font-semibold text-slate-500">{{ $invoice->due_date->format(Billmora::getGeneral('company_date_format')) }}</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 sm:gap-0 items-center">
                <div class="grid col-span-8">
                    <span class="font-semibold text-slate-600">{{ __('client/invoice.bill_to') }}</span>
                    <span class="font-semibold text-slate-500">{{ Billmora::getGeneral('company_name') }}</span>
                </div>
                <div class="grid col-span-4">
                    <span class="font-semibold text-slate-600">{{ __('client/invoice.issued_to') }}</span>
                    <span class="font-semibold text-slate-500">{{ $invoice->user->fullname }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                        <div class="flex justify-between bg-white items-center p-4">
                            <h5 class="text-lg font-semibold text-slate-600">{{ __('client/invoice.invoice_items') }}</h5>
                        </div>
                        <table class="min-w-full divide-y-2 divide-billmora-2">
                            <thead class="bg-billmora-2">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/invoice.description') }}</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/invoice.quantity') }}</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/invoice.unit_price') }}</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/invoice.amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-billmora-2 bg-white">
                                @foreach ($invoice->items as $items)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $items->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $items->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ Currency::format($items->unit_price, $invoice->currency) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ Currency::format($items->amount, $invoice->currency) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-white">
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <th class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/invoice.total') }}</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ Currency::format($invoice->total, $invoice->currency) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full lg:w-2/7 h-fit bg-white border-2 border-billmora-2 rounded-2xl">
        <div class="grid bg-billmora-primary p-6 rounded-xl">
            <div class="grid">
                <span class="text-md font-semibold text-white">{{ __('client/invoice.total_due') }}</span>
                <span class="text-2xl font-bold text-white">{{ Currency::format($invoice->total, $invoice->currency) }}</span>
            </div>
            <hr class="border-t-2 border-billmora-2 my-4">
            <div class="grid">
                <span class="text-md font-semibold text-white">{{ __('common.status') }}</span>
                @switch($invoice->status)
                    @case('paid')
                        <span class="text-2xl uppercase font-bold text-green-500">{{ __('client/invoice.status.paid') }}</span>
                        @break
                    @case('cancelled')
                        <span class="text-2xl uppercase font-bold text-slate-300">{{ __('client/invoice.status.cancelled') }}</span>
                        @break
                    @case('refunded')
                        <span class="text-2xl uppercase font-bold text-slate-300">{{ __('client/invoice.status.refunded') }}</span>
                        @break
                    @default
                        <span class="text-2xl uppercase font-bold text-red-500">{{ __('client/invoice.status.unpaid') }}</span>
                @endswitch
            </div>
        </div>
        @if ($invoice->status === 'unpaid')
            <form action="#" class="grid gap-6 p-6">
                <x-client::select
                    name="payment_gateway"
                    label="Payment Method"
                >
                    {{-- TODO: Get list payment method --}}
                </x-client::select>
                <button type="submit" class="w-full bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    Proceed to Payment
                </button>
            </form>
        @endif
    </div>
</div>
@endsection