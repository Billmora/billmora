@extends('client::layouts.app')

@section('title', 'Account Credits')

@section('body')
<div class="flex flex-col md:flex-row gap-5">
    <div class="w-full lg:w-2/3">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @forelse ($credits as $credit)
                <div class="grid w-full bg-billmora-bg p-6 border-b-4 border-green-500 rounded-lg">
                    <span class="text-md font-medium text-slate-500">{{ $credit->currency }}</span>
                    <span class="text-xl font-bold text-green-500">{{ Currency::format($credit->balance, $credit->currency)}}</span>
                </div>
            @empty
            <div class="w-full col-span-full bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl text-center">
                <span class="text-slate-500 font-semibold">{{ __('client/account.credits.no_credits') }}</span>
            </div>
            @endforelse
        </div>  
    </div>
    <form action="{{ route('client.account.credits.deposit') }}" method="POST" class="w-full lg:w-1/3 grid grid-cols-1 gap-4 bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
        @csrf
        <x-client::select 
            name="credit_currency"
            label="{{ __('client/account.credits.currency_label') }}"
            required
        >
            @foreach ($currencies as $currency)
                <option value="{{ $currency->code }}" {{ old('credit_currency') === $currency->code ? 'selected' : '' }}>
                    {{ $currency->code }}
                </option>
            @endforeach
        </x-client::select>
        <x-client::input 
            type="number"
            step="0.01"
            name="credit_amount"
            label="{{ __('client/account.credits.amount_label') }}"
            required
        />
        <x-client::select
            name="credit_payment_method"
            label="{{ __('client/account.credits.payment_method_label') }}"
            required
        >
            @foreach($gateways as $gateway)
                <option value="{{ $gateway->id }}" {{ old('credit_payment_method') == $gateway->id ? 'selected' : '' }}>
                    {{ $gateway->name }}
                </option>
            @endforeach
        </x-client::select>
        <button type="submit" class="w-full bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('client/account.credits.deposit_submit') }}
        </button>
    </form>
</div>
@endsection