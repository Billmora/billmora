@extends('admin::layouts.app')

@section('title', 'Transaction Create - Invoice')

@section('body')
<form action="{{ route('admin.invoices.transaction.create', ['invoice' => $invoice->invoice_number]) }}" method="POST" class="flex flex-col gap-5">
    @csrf
    <div class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin::singleselect 
                name="transaction_user"
                label="{{ __('admin/transactions.user_label') }}"
                helper="{{ __('admin/transactions.user_helper') }}"
                :options="$users->map(fn($user) => [
                    'value' => $user->id,
                    'title' => $user->email,
                    'subtitle' => $user->fullname
                ])->toArray()"
                :selected="old('transaction_user', $invoice->user_id)"
                required
            />
            <x-admin::input 
                name="transaction_invoice"
                label="{{ __('admin/transactions.invoice_label') }}"
                helper="{{ __('admin/transactions.invoice_helper') }}"
                value="{{ $invoice->invoice_number }}"
                required
                disabled
            />
            <x-admin::singleselect 
                name="transaction_gateway"
                label="{{ __('admin/transactions.gateway_label') }}"
                helper="{{ __('admin/transactions.gateway_helper') }}"
                :options="$plugins->map(fn($plugin) => [
                    'value' => $plugin->id,
                    'title' => $plugin->name,
                    'subtitle' => $plugin->provider,
                ])->toArray()"
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
        <a href="{{ route('admin.invoices.transaction', ['invoice' => $invoice->invoice_number]) }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
    </div>
</form>
@endsection