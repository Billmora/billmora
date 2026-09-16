@extends('admin::layouts.app')

@section('title', __('admin/affiliate.withdrawals_title'))

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

    <div class="flex gap-2">
        <a href="{{ route('admin.modules.affiliate.withdrawals') }}" class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ !request('status') ? 'bg-billmora-primary-500 text-white' : 'bg-billmora-2 text-slate-600 hover:bg-billmora-primary-500 hover:text-white' }} transition-colors duration-150">{{ __('admin/affiliate.filters.all') }}</a>
        <a href="{{ route('admin.modules.affiliate.withdrawals', ['status' => 'pending']) }}" class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ request('status') === 'pending' ? 'bg-billmora-primary-500 text-white' : 'bg-billmora-2 text-slate-600 hover:bg-billmora-primary-500 hover:text-white' }} transition-colors duration-150">{{ __('admin/affiliate.filters.pending') }}</a>
        <a href="{{ route('admin.modules.affiliate.withdrawals', ['status' => 'approved']) }}" class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ request('status') === 'approved' ? 'bg-billmora-primary-500 text-white' : 'bg-billmora-2 text-slate-600 hover:bg-billmora-primary-500 hover:text-white' }} transition-colors duration-150">{{ __('admin/affiliate.filters.approved') }}</a>
        <a href="{{ route('admin.modules.affiliate.withdrawals', ['status' => 'rejected']) }}" class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ request('status') === 'rejected' ? 'bg-billmora-primary-500 text-white' : 'bg-billmora-2 text-slate-600 hover:bg-billmora-primary-500 hover:text-white' }} transition-colors duration-150">{{ __('admin/affiliate.filters.rejected') }}</a>
    </div>

    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/affiliate.withdrawals.columns.affiliate') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/affiliate.withdrawals.columns.amount') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/affiliate.withdrawals.columns.method') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/affiliate.withdrawals.columns.detail') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/affiliate.withdrawals.columns.requested') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @forelse ($withdrawals as $withdrawal)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $withdrawal->member->user->email ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $withdrawal->currency }} {{ number_format($withdrawal->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $withdrawal->method ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-800 max-w-xs truncate">{{ $withdrawal->detail ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($withdrawal->status === 'approved')
                                    <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">{{ __('admin/affiliate.withdrawals.status.approved') }}</span>
                                @elseif($withdrawal->status === 'pending')
                                    <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-amber-100 text-amber-800">{{ __('admin/affiliate.withdrawals.status.pending') }}</span>
                                @else
                                    <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-red-100 text-red-800">{{ __('admin/affiliate.withdrawals.status.rejected') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $withdrawal->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                @if($withdrawal->status === 'pending')
                                    <form action="{{ route('admin.modules.affiliate.withdrawals.approve', $withdrawal) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-500 hover:text-green-600 font-semibold cursor-pointer">{{ __('admin/affiliate.withdrawals.actions.approve') }}</button>
                                    </form>
                                    <form action="{{ route('admin.modules.affiliate.withdrawals.reject', $withdrawal) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-red-400 hover:text-red-500 font-semibold cursor-pointer">{{ __('admin/affiliate.withdrawals.actions.reject') }}</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">{{ __('common.no_data') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        {{ $withdrawals->links('admin::layouts.partials.pagination') }}
    </div>
</div>
@endsection
