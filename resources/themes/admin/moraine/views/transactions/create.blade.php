@extends('admin::layouts.app')

@section('title', 'Transaction Create')

@section('body')
<form action="{{ route('admin.transactions.store') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    <div class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin::singleselect 
                name="transaction_user"
                label="{{ __('admin/transactions.user_label') }}"
                helper="{{ __('admin/transactions.user_helper') }}"
                :options="$userOptions"
                :selected="old('transaction_user')"
                required
            />
            <x-admin::singleselect 
                name="transaction_invoice"
                label="{{ __('admin/transactions.invoice_label') }}"
                helper="{{ __('admin/transactions.invoice_helper') }}"
                :options="$invoiceOptions"
                :selected="old('transaction_invoice')"
                required
            />
            <x-admin::singleselect 
                name="transaction_gateway"
                label="{{ __('admin/transactions.gateway_label') }}"
                helper="{{ __('admin/transactions.gateway_helper') }}"
                :options="$pluginOptions"
                :selected="old('transaction_gateway')"
            />
            <x-admin::input 
                name="transaction_reference"
                label="{{ __('admin/transactions.reference_label') }}"
                helper="{{ __('admin/transactions.reference_helper') }}"
                value="{{ old('transaction_reference') }}"
            />
            <x-admin::input 
                name="transaction_amount"
                type="number"
                label="{{ __('admin/transactions.amount_label') }}"
                helper="{{ __('admin/transactions.amount_helper') }}"
                value="{{ old('transaction_amount') }}"
                step="0.01"
                required
            />
            <x-admin::input 
                name="transaction_fee"
                type="number"
                label="{{ __('admin/transactions.fee_label') }}"
                helper="{{ __('admin/transactions.fee_helper') }}"
                value="{{ old('transaction_fee') }}"
                step="0.01"
                required
            />
        </div>
        <x-admin::textarea
            name="transaction_description"
            label="{{ __('admin/transactions.description_label') }}"
            helper="{{ __('admin/transactions.description_helper') }}"
            required
        >{{ old('transaction_description') }}</x-admin::textarea>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.transactions') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
    </div>
</form>
@endsection