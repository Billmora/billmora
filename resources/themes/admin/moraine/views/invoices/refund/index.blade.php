@extends('admin::layouts.app')

@section('title', "Invoice Refund - {$invoice->invoice_number}")

@section('body')
<div class="flex flex-col gap-5">
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
            [
                'route' => route('admin.invoices.refund', ['invoice' => $invoice->invoice_number]),
                'icon' => 'lucide-banknote',
                'label' => __('admin/invoices.tabs.refund'),
            ],
        ]" 
        active="{{ request()->url() }}"
    />
    <form action="{{ route('admin.invoices.refund.store', ['invoice' => $invoice->invoice_number]) }}" method="POST" class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        @csrf
        <div 
            class="grid grid-cols-1 md:grid-cols-2 gap-4"
            x-data="{ refundType: '{{ old('refund_type', 'gateway') }}' }"
            x-init="$watch('refundType', value => $el.querySelector('[name=refund_type]').value = value)"
        >
            <x-admin::select
                name="refund_type"
                label="{{ __('admin/invoices.refund.type_label') }}"
                helper="{{ __('admin/invoices.refund.type_helper') }}"
                x-model="refundType"
                required
            >
                <option value="gateway" {{ old('refund_type') === 'gateway' ? 'selected' : '' }}>{{ __('admin/invoices.refund.type_gateway') }}</option>
                <option value="manual" {{ old('refund_type') === 'manual' ? 'selected' : '' }}>{{ __('admin/invoices.refund.type_manual') }}</option>
            </x-admin::select>
            <x-admin::input 
                name="refund_amount"
                label="{{ __('admin/invoices.refund.amount_label') }}"
                helper="{{ __('admin/invoices.refund.amount_helper') }}"
                step="0.01"
                required
            />
            <div x-show="refundType === 'manual'" x-cloak>
                <x-admin::input 
                    name="refund_reference"
                    label="{{ __('admin/invoices.refund.reference_label') }}"
                    helper="{{ __('admin/invoices.refund.reference_helper') }}"
                />
            </div>
        </div>
        <div class="flex gap-4 ml-auto">
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.submit') }}
            </button>
        </div>
    </form>
</div>
@endsection