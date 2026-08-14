@extends('admin::layouts.app')

@section('title', __('admin/affiliate.overview_title'))

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs :tabs="[
        [
            'route' => route('admin.modules.affiliate.index'),
            'icon'  => 'lucide-layout-dashboard',
            'label' => __('admin/affiliate.tabs.overview'),
        ],
        [
            'route' => route('admin.modules.affiliate.members'),
            'icon'  => 'lucide-users',
            'label' => __('admin/affiliate.tabs.members'),
        ],
        [
            'route' => route('admin.modules.affiliate.commissions'),
            'icon'  => 'lucide-coins',
            'label' => __('admin/affiliate.tabs.commissions'),
        ],
        [
            'route' => route('admin.modules.affiliate.withdrawals'),
            'icon'  => 'lucide-wallet',
            'label' => __('admin/affiliate.tabs.withdrawals'),
        ],
    ]" active="{{ request()->url() }}" />

    <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="flex items-center gap-4 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-blue-200 p-3 text-blue-500 rounded-full">
                <x-lucide-users class="w-auto h-10" />
            </div>
            <div>
                <h4 class="text-lg font-semibold text-slate-500">{{ __('admin/affiliate.overview.total_members') }}</h4>
                <span class="text-2xl font-semibold text-slate-600">{{ $totalMembers }}</span>
                <span class="text-sm text-slate-400 ml-1">{{ __('admin/affiliate.overview.active', ['count' => $activeMembers]) }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-green-200 p-3 text-green-500 rounded-full">
                <x-lucide-user-plus class="w-auto h-10" />
            </div>
            <div>
                <h4 class="text-lg font-semibold text-slate-500">{{ __('admin/affiliate.overview.referrals') }}</h4>
                <span class="text-2xl font-semibold text-slate-600">{{ $totalReferrals }}</span>
                <span class="text-sm text-slate-400 ml-1">{{ __('admin/affiliate.overview.converted', ['count' => $convertedReferrals]) }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-violet-200 p-3 text-violet-500 rounded-full">
                <x-lucide-coins class="w-auto h-10" />
            </div>
            <div>
                <h4 class="text-lg font-semibold text-slate-500">{{ __('admin/affiliate.overview.total_commissions') }}</h4>
                <span class="text-2xl font-semibold text-slate-600">{{ number_format($totalCommissions, 2) }}</span>
                <span class="text-sm text-slate-400 ml-1">{{ __('admin/affiliate.overview.pending', ['count' => $pendingCommissions]) }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
            <div class="bg-amber-200 p-3 text-amber-500 rounded-full">
                <x-lucide-wallet class="w-auto h-10" />
            </div>
            <div>
                <h4 class="text-lg font-semibold text-slate-500">{{ __('admin/affiliate.overview.pending_withdrawals') }}</h4>
                <span class="text-2xl font-semibold text-slate-600">{{ $pendingWithdrawals }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
