@extends('admin::layouts.app')

@section('title', "Invoice Edit - {$invoice->invoice_number}")

@section('body')
<div class="grid gap-4">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.invoices.edit', ['invoice' => $invoice->id]),
                'icon' => 'lucide-receipt-text',
                'label' => __('admin/invoices.tabs.summary'),
            ],
            [
                'route' => route('admin.invoices.transaction', ['invoice' => $invoice->id]),
                'icon' => 'lucide-landmark',
                'label' => __('admin/invoices.tabs.transaction'),
            ],
            [
                'route' => route('admin.invoices.refund', ['invoice' => $invoice->id]),
                'icon' => 'lucide-banknote',
                'label' => __('admin/invoices.tabs.refund'),
            ],
        ]" 
        active="{{ request()->url() }}"
    />
    <a href="{{ route('admin.invoices.download', ['invoice' => $invoice->id]) }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer">
        {{ __('admin/invoices.download_label') }}
    </a>
    @livewire('admin.invoices.invoice-edit', ['invoice' => $invoice])
</div>
@endsection