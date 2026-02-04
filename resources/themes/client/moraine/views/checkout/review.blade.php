@extends('client::layouts.app')

@section('title', 'Checkout Review')

@section('body')
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
            <div class="bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <h1 class="text-xl font-semibold text-slate-600">
                    {{ $package->catalog->name }} - {{ $package->name }}
                </h1>
                @if(!empty($variants))
                    <div class="mt-4 space-y-2">
                        @foreach ($variants as $variant)
                            <div class="flex items-start gap-3">
                                <span class="text-slate-400 font-medium">{{ $variant['name'] }}:</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($variant['options'] as $option)
                                        <span class="inline-block px-3 py-1 text-sm bg-billmora-2 text-billmora-primary font-medium rounded-full">
                                            {{ $option['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="bg-billmora-primary p-8 border-2 border-billmora-2 rounded-2xl">
                <label for="coupon_code_input" class="block text-white font-semibold mb-1">{{ __('client/checkout.coupon_label') }}</label>
                <div class="flex gap-4">
                    <input
                        type="text"
                        id="coupon_code_input"
                        class="w-full bg-white px-3 py-2 rounded-lg border-2 border-billmora-2 outline-none text-slate-700 placeholder:text-slate-500 focus:ring-2 ring-billmora-primary {{ !empty($appliedCoupon) ? 'bg-gray-100' : '' }}"
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
                            class="w-auto bg-violet-100 hover:bg-violet-200 px-3 py-2 text-billmora-primary font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                            onclick="document.getElementById('coupon_code_hidden').value = document.getElementById('coupon_code_input').value;"
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
            @if (Billmora::getGeneral('ordering_notes'))
                <div class="bg-white px-8 py-7 border-2 border-billmora-2 rounded-2xl">
                    <label for="notes" class="block text-slate-600 font-semibold mb-1">
                        {{ __('client/checkout.notes_label') }}
                    </label>
                    <textarea 
                        name="notes"
                        id="notes"
                        rows="6"
                        class="w-full bg-white text-slate-700 rounded-lg px-3 py-2 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary placeholder:text-slate-500"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="text-sm text-red-400 font-semibold">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            @endif
        </div>
        <div class="w-full lg:w-1/3 h-fit flex flex-col gap-4">
            <div class="bg-white p-8 border-2 border-billmora-2 rounded-2xl space-y-4">
                <h2 class="text-xl font-semibold text-slate-600 mb-4">{{ __('client/checkout.order_summary') }}</h2>
                <div class="grid font-medium">
                    <div class="flex gap-3 justify-between">
                        <span class="text-slate-500 text-start">{{ $packagePrice->name }}</span>
                        <span class="text-slate-600 font-semibold text-end">
                            {{ Currency::format($pricing['base_price']) }}
                        </span>
                    </div>
                    @foreach($pricing['variant_items'] as $item)
                        @if($item['price'] > 0)
                            <div class="flex gap-3 justify-between mt-1">
                                <span class="text-slate-500 text-sm text-start">- {{ $item['description'] }}</span>
                                <span class="text-slate-600 text-sm font-semibold text-end">
                                    {{ Currency::format($item['price']) }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                    <hr class="border-t-2 border-billmora-2 my-4">
                    <div class="flex gap-3 justify-between">
                        <span class="text-slate-600 font-semibold text-start">{{ __('client/checkout.subtotal') }}</span>
                        <span class="text-slate-600 font-semibold text-end">
                            {{ Currency::format($pricing['subtotal']) }}
                        </span>
                    </div>
                    @if($pricing['setup_fee_total'] > 0)
                        <div class="flex gap-3 justify-between mt-2">
                            <span class="text-slate-600 font-semibold text-start">{{ __('client/checkout.setup_fee') }}</span>
                            <span class="text-slate-600 font-semibold text-end">
                                {{ Currency::format($pricing['setup_fee_total']) }}
                            </span>
                        </div>
                    @endif
                    @if($pricing['discount'] > 0)
                        <div class="flex gap-3 justify-between mt-2 text-billmora-primary">
                            <span class="font-semibold text-start">{{ __('client/checkout.discount') }}</span>
                            <span class="font-semibold text-end">
                                - {{ Currency::format($pricing['discount']) }}
                            </span>
                        </div>
                    @endif
                    <hr class="border-t-2 border-billmora-2 my-4">
                    <div class="flex gap-3 justify-between">
                        <span class="text-billmora-primary font-bold text-lg text-start">{{ __('client/checkout.total_due') }}</span>
                        <span class="text-billmora-primary font-bold text-lg text-end">
                            {{ Currency::format($pricing['total']) }}
                        </span>
                    </div>
                </div>
                <button type="submit" class="w-full bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-3 text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('client/checkout.complete_order') }}
                </button>
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
                            class="w-4 h-4 accent-billmora-primary cursor-pointer"
                        >
                        <label for="terms_accepted" class="text-slate-600 font-medium cursor-pointer">
                            {!! __('client/checkout.agree_terms', [
                                'attribute' => '<a href="' . (Billmora::getGeneral('term_tos_url') ?? '#') . '" target="_blank" class="text-billmora-primary underline">Terms and Conditions.</a>'
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
@endsection