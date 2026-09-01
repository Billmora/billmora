@extends('client::layouts.app')

@section('title', __('client/affiliate.dashboard_title'))

@section('body')
<div class="flex flex-col gap-6">
    <div class="grid gap-1">
        <h1 class="text-2xl font-bold text-slate-700">{{ __('client/affiliate.dashboard_title') }}</h1>
        <p class="text-slate-500 text-sm">{{ __('client/affiliate.dashboard_subtitle') }}</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-none md:grid-cols-3 gap-5">
        <div class="flex items-center gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-green-200 p-3 text-green-500 rounded-full">
                <x-lucide-wallet class="w-auto h-8" />
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-500">{{ __('client/affiliate.stats.available_balance') }}</h4>
                <span class="text-2xl font-bold text-slate-700">{{ Currency::format($member->balance, $defaultCurrency) }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-violet-200 p-3 text-violet-500 rounded-full">
                <x-lucide-coins class="w-auto h-8" />
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-500">{{ __('client/affiliate.stats.total_earned') }}</h4>
                <span class="text-2xl font-bold text-slate-700">{{ Currency::format($member->total_earned, $defaultCurrency) }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-blue-200 p-3 text-blue-500 rounded-full">
                <x-lucide-user-plus class="w-auto h-8" />
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-500">{{ __('client/affiliate.stats.referrals') }}</h4>
                <span class="text-2xl font-bold text-slate-700">{{ $member->referrals_count }}</span>
            </div>
        </div>
    </div>

    {{-- Referral Link --}}
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-3">{{ __('client/affiliate.referral_link.title') }}</h3>
        <x-client::input type="text" name="referral_link" label="{{ __('client/affiliate.referral_link.label') }}" value="{{ $referralUrl }}" readonly />
        <p class="text-xs text-slate-400 mt-2">{{ __('client/affiliate.referral_link.share_info', ['code' => $member->referral_code]) }}</p>
    </div>

    {{-- Withdrawal Request --}}
    @if((float) $member->balance > 0)
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-3">{{ __('client/affiliate.withdrawal.title') }}</h3>
        <form action="{{ route('client.modules.affiliate.withdrawal.store') }}" method="POST" class="grid grid-cols-none md:grid-cols-3 gap-4">
            @csrf
            <x-client::input type="number" name="amount" label="{{ __('client/affiliate.withdrawal.amount_label') }}" helper="{{ __('client/affiliate.withdrawal.amount_helper') }}" step="0.01" required />
            <x-client::input type="text" name="method" label="{{ __('client/affiliate.withdrawal.method_label') }}" helper="{{ __('client/affiliate.withdrawal.method_helper') }}" required />
            <x-client::input type="text" name="detail" label="{{ __('client/affiliate.withdrawal.detail_label') }}" helper="{{ __('client/affiliate.withdrawal.detail_helper') }}" />
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 text-white font-semibold rounded-lg transition-colors duration-150 cursor-pointer">
                    {{ __('client/affiliate.withdrawal.submit') }}
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Recent Referrals --}}
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-4">{{ __('client/affiliate.recent_referrals.title') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-billmora-2">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_referrals.user') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_referrals.converted') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_referrals.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-billmora-2">
                    @forelse ($referrals as $referral)
                    <tr>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ Str::mask($referral->referredUser->email ?? '-', '*', 3, 5) }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($referral->converted)
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">{{ __('client/affiliate.status.yes') }}</span>
                            @else
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-slate-100 text-slate-600">{{ __('client/affiliate.status.no') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $referral->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-sm text-slate-500 text-center">{{ __('client/affiliate.recent_referrals.no_data') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Commissions --}}
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-4">{{ __('client/affiliate.recent_commissions.title') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-billmora-2">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_commissions.invoice') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_commissions.amount') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_commissions.status') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_commissions.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-billmora-2">
                    @forelse ($commissions as $commission)
                    <tr>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $commission->invoice->invoice_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ Currency::format($commission->amount, $commission->currency) }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($commission->status === 'approved')
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">{{ __('client/affiliate.status.approved') }}</span>
                            @elseif($commission->status === 'pending')
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-amber-100 text-amber-800">{{ __('client/affiliate.status.pending') }}</span>
                            @else
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-red-100 text-red-800">{{ __('client/affiliate.status.rejected') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $commission->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm text-slate-500 text-center">{{ __('client/affiliate.recent_commissions.no_data') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Withdrawals --}}
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-4">{{ __('client/affiliate.recent_withdrawals.title') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-billmora-2">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_withdrawals.amount') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_withdrawals.method') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_withdrawals.status') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/affiliate.recent_withdrawals.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-billmora-2">
                    @forelse ($withdrawals as $withdrawal)
                    <tr>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ Currency::format($withdrawal->amount, $withdrawal->currency) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $withdrawal->method ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($withdrawal->status === 'approved')
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">{{ __('client/affiliate.status.approved') }}</span>
                            @elseif($withdrawal->status === 'pending')
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-amber-100 text-amber-800">{{ __('client/affiliate.status.pending') }}</span>
                            @else
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-red-100 text-red-800">{{ __('client/affiliate.status.rejected') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $withdrawal->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm text-slate-500 text-center">{{ __('client/affiliate.recent_withdrawals.no_data') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
