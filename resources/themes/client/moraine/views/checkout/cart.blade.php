@extends('client::layouts.app')

@section('title', 'Shopping Cart')

@section('body')
@if(empty($cartItems))
    <div class="bg-billmora-bg p-12 border-2 border-billmora-2 rounded-2xl text-center">
        <h2 class="text-2xl font-bold text-slate-600 mb-4">{{ __('client/checkout.cart.empty') }}</h2>
        <p class="text-slate-500 mb-6">{{ __('client/checkout.cart.empty_message') }}</p>
        <a href="{{ route('client.store') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-3 text-white font-semibold rounded-lg transition-colors">
            {{ __('client/store.view_store') }}
        </a>
    </div>
@else
    <form id="coupon-check-form" action="{{ route('client.checkout.coupon.check') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="coupon_code" id="coupon_code_hidden" value="">
    </form>
    <form id="coupon-remove-form" action="{{ route('client.checkout.coupon.remove') }}" method="POST" class="hidden">
        @csrf
    </form>
    <div class="grid gap-5">
        <form action="{{ route('client.checkout.process') }}" method="POST" class="flex flex-col lg:flex-row gap-5">
            @csrf
            <div class="w-full lg:w-2/3 h-fit grid gap-5">
                <div class="flex flex-col sm:flex-row justify-between gap-4">
                    <h1 class="text-2xl font-semibold text-slate-700">{{ __('client/checkout.cart.review_order') }}</h1>
                    <a href="{{ route('client.store') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 ml-auto px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('client/checkout.cart.contiue_shopping') }}
                    </a>
                </div>
                @foreach($cartItems as $id => $item)
                    <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
                        <div class="flex flex-col md:flex-row justify-between gap-6">
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold text-slate-600">{{ $item['description'] }}</h2>
                                <p class="text-slate-500 font-medium">{{ $item['cycle_name'] }}</p>
                                @if(!empty($item['variant_details']))
                                    <div class="mt-4 space-y-2">
                                        @foreach ($item['variant_details'] as $variant)
                                            <div class="flex items-center gap-2">
                                                <span class="text-slate-400 font-medium">{{ $variant['name'] }}:</span>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($variant['options'] as $option)
                                                        <span class="inline-block px-3 py-1 text-sm bg-billmora-2 text-billmora-primary-500 font-medium rounded-full">
                                                            {{ $option['name'] }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col items-end justify-between gap-4">
                                <div class="text-right">
                                    <span class="text-xl font-bold text-slate-700">{{ Currency::format($item['unit_price'] * $item['quantity']) }}</span>
                                    @if($item['setup_fee'] > 0)
                                        <p class="text-sm font-medium text-slate-500">+ {{ Currency::format($item['setup_fee'] * $item['quantity']) }} {{ __('client/checkout.setup_fee') }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col sm:flex-row gap-4">
                                    @if($item['allow_quantity'] === 'multiple')
                                        <input type="number" form="update-qty-{{ $id }}" name="quantity" value="{{ $item['quantity'] }}" min="1" class="w-20 px-2 py-1 text-center bg-billmora-bg border-2 border-billmora-2 rounded-lg outline-none focus:ring-2 ring-billmora-primary-500 font-semibold text-slate-700" onchange="document.getElementById('update-qty-{{ $id }}').submit();">
                                    @endif  
                                    <button type="submit" form="remove-item-{{ $id }}" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                                        {{ __('common.remove') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="bg-billmora-primary-500 p-8 border-2 border-billmora-2 rounded-2xl">
                    <label for="coupon_code_input" class="block text-white font-semibold mb-1">{{ __('client/checkout.coupon_label') }}</label>
                    <div class="flex gap-4">
                        <input
                            type="text"
                            id="coupon_code_input"
                            class="w-full bg-billmora-bg px-3 py-2 rounded-lg border-2 border-billmora-2 outline-none text-slate-700 placeholder:text-slate-500 focus:ring-2 ring-billmora-primary-500 {{ !empty($appliedCoupon) ? 'bg-gray-100' : '' }}"
                            value="{{ old('coupon_code', $appliedCoupon['code'] ?? '') }}"
                            {{ !empty($appliedCoupon) ? 'readonly' : '' }}
                        />
                        @if(!empty($appliedCoupon))
                            <button
                                type="submit"
                                form="coupon-remove-form"
                                class="w-auto bg-red-100 hover:bg-red-200 px-3 py-2 text-red-500 font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                            >
                                {{ __('common.remove') }}
                            </button>
                        @else
                            <button
                                type="submit"
                                form="coupon-check-form"
                                class="w-auto bg-violet-100 hover:bg-violet-200 px-3 py-2 text-billmora-primary-500 font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                            >
                                {{ __('common.apply') }}
                            </button>
                        @endif
                    </div>
                    @error('coupon_code')
                        <span class="mt-1 text-sm text-red-400 font-semibold">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
                    <x-client::select
                        name="payment_method"
                        label="{{ __('client/checkout.payment_label') }}"
                        :value="old('payment_method')"
                        required
                    >
                        @foreach($gateways as $gateway)
                            <option value="{{ $gateway->id }}" {{ old('payment_method') == $gateway->id ? 'selected' : '' }}>
                                {{ $gateway->name }}
                            </option>
                        @endforeach
                    </x-client::select>
                </div>
                @if (Billmora::getGeneral('ordering_notes'))
                <div class="bg-billmora-bg px-8 py-7 border-2 border-billmora-2 rounded-2xl">
                        <label for="notes" class="block text-slate-600 font-semibold mb-1">
                            {{ __('client/checkout.notes_label') }}
                        </label>
                        <textarea 
                            name="notes"
                            id="notes"
                            rows="6"
                            class="w-full bg-billmora-bg text-slate-700 rounded-lg px-3 py-2 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary-500 placeholder:text-slate-500"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="text-sm text-red-400 font-semibold">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                @endif
            </div>
            <div class="w-full lg:w-1/3 h-fit flex flex-col gap-5 sticky top-5">
                <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl space-y-5">
                    <h2 class="text-xl font-semibold text-slate-600 mb-4">{{ __('client/checkout.order_summary') }}</h2>
                    <div class="grid font-medium gap-3">
                        <div class="flex justify-between text-slate-500">
                            <span>Items ({{ $totals['count'] }})</span>
                            <span class="text-slate-700 font-semibold">{{ Currency::format($totals['subtotal']) }}</span>
                        </div>
                        @if($totals['setup_fee'] > 0)
                            <div class="flex justify-between text-slate-500">
                                <span>{{ __('client/checkout.setup_fee') }}</span>
                                <span class="text-slate-700 font-semibold">{{ Currency::format($totals['setup_fee']) }}</span>
                            </div>
                        @endif
                        @if($totals['discount'] > 0)
                            <div class="flex gap-3 justify-between text-billmora-primary-500">
                                <span class="font-semibold text-start">{{ __('client/checkout.discount') }}</span>
                                <span class="font-semibold text-end">
                                    - {{ Currency::format($totals['discount']) }}
                                </span>
                            </div>
                        @endif
                        @if(isset($totals['tax']) && $totals['tax'] > 0)
                            <div class="flex justify-between text-slate-500">
                                <span>{{ $totals['tax_name'] ?? 'Tax' }}</span>
                                <span class="text-slate-700 font-semibold">{{ Currency::format($totals['tax']) }}</span>
                            </div>
                        @endif
                        <hr class="border-t-2 border-billmora-2">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-bold text-lg">{{ __('client/checkout.total_due') }}</span>
                            <span class="text-billmora-primary-500 font-bold text-2xl">
                                {{ Currency::format($totals['total']) }}
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-3 text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('client/checkout.complete_order') }}
                    </button>
                    <x-client::captcha form="checkout_form" class="text-center mx-auto" />
                </div>
                @if (Billmora::getGeneral('ordering_tos'))
                <div class="flex flex-col gap-0">
                        <div class="flex items-center gap-2 mx-auto">
                            <input 
                                name="terms_accepted" 
                                id="terms_accepted" 
                                type="checkbox" 
                                value="1"
                                {{ old('terms_accepted') ? 'checked' : '' }}
                                class="w-4 h-4 accent-billmora-primary-500 cursor-pointer"
                            >
                            <label for="terms_accepted" class="text-slate-600 font-medium cursor-pointer">
                                {!! __('client/checkout.agree_terms', [
                                    'attribute' => '<a href="' . (Billmora::getGeneral('term_tos_url') ?? '#') . '" target="_blank" class="text-billmora-primary-500 underline">Terms and Conditions.</a>'
                                ]) !!}
                            </label>
                        </div>
                        @error('terms_accepted')
                            <span class="mt-1 text-sm text-red-400 text-center font-semibold">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                @endif
            </div>
        </form>
    </div>
    @foreach($cartItems as $id => $item)
        <form id="remove-item-{{ $id }}" action="{{ route('client.checkout.cart.remove', $id) }}" method="POST" class="hidden">
            @csrf
        </form>
        @if($item['allow_quantity'] === 'multiple')
            <form id="update-qty-{{ $id }}" action="{{ route('client.checkout.cart.update', $id) }}" method="POST" class="hidden">
                @csrf
            </form>
        @endif
    @endforeach
@endif
@endsection