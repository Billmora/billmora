@extends('client::layouts.app')

@section('title', 'Affiliate Dashboard')

@section('body')
<div class="flex flex-col gap-6">
    <div class="grid gap-1">
        <h1 class="text-2xl font-bold text-slate-700">Affiliate Dashboard</h1>
        <p class="text-slate-500 text-sm">Manage your referrals, track commissions, and request withdrawals.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-none md:grid-cols-3 gap-5">
        <div class="flex items-center gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-green-200 p-3 text-green-500 rounded-full">
                <x-lucide-wallet class="w-auto h-8" />
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-500">Available Balance</h4>
                <span class="text-2xl font-bold text-slate-700">{{ Currency::format($member->balance, $defaultCurrency) }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-violet-200 p-3 text-violet-500 rounded-full">
                <x-lucide-coins class="w-auto h-8" />
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-500">Total Earned</h4>
                <span class="text-2xl font-bold text-slate-700">{{ Currency::format($member->total_earned, $defaultCurrency) }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-blue-200 p-3 text-blue-500 rounded-full">
                <x-lucide-user-plus class="w-auto h-8" />
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-500">Referrals</h4>
                <span class="text-2xl font-bold text-slate-700">{{ $member->referrals_count }}</span>
            </div>
        </div>
    </div>

    {{-- Referral Link --}}
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-3">Your Referral Link</h3>
        <x-client::input type="text" name="referral_link" label="Referral Link" value="{{ $referralUrl }}" readonly />
        <p class="text-xs text-slate-400 mt-2">Share this link to earn commissions. Code: <span class="font-mono font-semibold text-slate-600">{{ $member->referral_code }}</span></p>
    </div>

    {{-- Withdrawal Request --}}
    @if((float) $member->balance > 0)
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-3">Request Withdrawal</h3>
        <form action="{{ route('client.modules.affiliate.withdrawal.store') }}" method="POST" class="grid grid-cols-none md:grid-cols-3 gap-4">
            @csrf
            <x-client::input type="number" name="amount" label="Amount" helper="Enter the amount you'd like to withdraw." step="0.01" required />
            <x-client::input type="text" name="method" label="Method" helper="e.g., Bank Transfer, PayPal, Crypto" required />
            <x-client::input type="text" name="detail" label="Detail" helper="e.g., Bank account number, PayPal email" />
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 text-white font-semibold rounded-lg transition-colors duration-150 cursor-pointer">
                    Submit Withdrawal
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Recent Referrals --}}
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-4">Recent Referrals</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-billmora-2">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">User</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Converted</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-billmora-2">
                    @forelse ($referrals as $referral)
                    <tr>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ Str::mask($referral->referredUser->email ?? '-', '*', 3, 5) }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($referral->converted)
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">Yes</span>
                            @else
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-slate-100 text-slate-600">No</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $referral->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-sm text-slate-500 text-center">No referrals yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Commissions --}}
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-4">Recent Commissions</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-billmora-2">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Invoice</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-billmora-2">
                    @forelse ($commissions as $commission)
                    <tr>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $commission->invoice->invoice_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ Currency::format($commission->amount, $commission->currency) }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($commission->status === 'approved')
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">Approved</span>
                            @elseif($commission->status === 'pending')
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                            @else
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $commission->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm text-slate-500 text-center">No commissions yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Withdrawals --}}
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-700 mb-4">Recent Withdrawals</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-billmora-2">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Method</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-billmora-2">
                    @forelse ($withdrawals as $withdrawal)
                    <tr>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ Currency::format($withdrawal->amount, $withdrawal->currency) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $withdrawal->method ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($withdrawal->status === 'approved')
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">Approved</span>
                            @elseif($withdrawal->status === 'pending')
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                            @else
                                <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $withdrawal->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm text-slate-500 text-center">No withdrawals yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
