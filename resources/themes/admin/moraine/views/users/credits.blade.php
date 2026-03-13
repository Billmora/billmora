@extends('admin::layouts.app')

@section('title', "User Credits - {$user->email}")

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.users.summary', ['user' => $user->id]),
                'icon' => 'lucide-contact',
                'label' => __('admin/users.tabs.summary'),
            ],
            [
                'route' => route('admin.users.profile', ['user' => $user->id]),
                'icon' => 'lucide-user-pen',
                'label' => __('admin/users.tabs.profile'),
            ],
            [
                'route' => route('admin.users.activity', ['user' => $user->id]),
                'icon' => 'lucide-activity',
                'label' => __('admin/users.tabs.activity'),
            ],
            [
                'route' => route('admin.users.credits', ['user' => $user->id]),
                'icon' => 'lucide-badge-cent',
                'label' => __('admin/users.tabs.credits'),
            ],
        ]" 
        active="{{ request()->url() }}" 
    />
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        @foreach ($currencies as $currency)
            <form action="{{ route('admin.users.credits.update', ['user' => $user->id, 'currency' => $currency->id]) }}" method="POST" class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid">
                        <span class="text-slate-500 font-semibold">{{ __('admin/users.credit_currency_label') }}</span>
                        <span class="text-lg text-slate-600 font-semibold">{{ $currency->code }}</span>
                    </div>
                    <div class="grid">
                        <span class="text-slate-500 font-semibold">{{ __('admin/users.credit_formatted_label') }}</span>
                        <span class="text-lg text-slate-600 font-semibold">{{ Currency::format($user->getCreditWallet($currency->code)->balance, $currency->code) }}</span>
                    </div>
                </div>
                <x-admin::input 
                    name="credit_balance"
                    type="number"
                    step="0.01"
                    label="{{ __('admin/users.credit_balance_label') }}"
                    helper="{{ __('admin/users.credit_balance_helper') }}"
                    value="{{ old('credit_balance', $user->getCreditWallet($currency->code)->balance) }}"
                    required
                />
                <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">
                    {{ __('common.save') }}
                </button>
            </form>
        @endforeach
    </div>
</div>
@endsection