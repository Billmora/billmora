@extends('admin::layouts.app')

@section('title', 'Affiliate Commissions')

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs :tabs="[
        [
            'route' => route('admin.modules.affiliate.index'),
            'icon'  => 'lucide-layout-dashboard',
            'label' => 'Overview',
        ],
        [
            'route' => route('admin.modules.affiliate.members'),
            'icon'  => 'lucide-users',
            'label' => 'Members',
        ],
        [
            'route' => route('admin.modules.affiliate.commissions'),
            'icon'  => 'lucide-coins',
            'label' => 'Commissions',
        ],
        [
            'route' => route('admin.modules.affiliate.withdrawals'),
            'icon'  => 'lucide-wallet',
            'label' => 'Withdrawals',
        ],
    ]" active="{{ request()->url() }}" />

    <div class="flex gap-2">
        <a href="{{ route('admin.modules.affiliate.commissions') }}" class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ !request('status') ? 'bg-billmora-primary-500 text-white' : 'bg-billmora-2 text-slate-600 hover:bg-billmora-primary-500 hover:text-white' }} transition-colors duration-150">All</a>
        <a href="{{ route('admin.modules.affiliate.commissions', ['status' => 'pending']) }}" class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ request('status') === 'pending' ? 'bg-billmora-primary-500 text-white' : 'bg-billmora-2 text-slate-600 hover:bg-billmora-primary-500 hover:text-white' }} transition-colors duration-150">Pending</a>
        <a href="{{ route('admin.modules.affiliate.commissions', ['status' => 'approved']) }}" class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ request('status') === 'approved' ? 'bg-billmora-primary-500 text-white' : 'bg-billmora-2 text-slate-600 hover:bg-billmora-primary-500 hover:text-white' }} transition-colors duration-150">Approved</a>
        <a href="{{ route('admin.modules.affiliate.commissions', ['status' => 'rejected']) }}" class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ request('status') === 'rejected' ? 'bg-billmora-primary-500 text-white' : 'bg-billmora-2 text-slate-600 hover:bg-billmora-primary-500 hover:text-white' }} transition-colors duration-150">Rejected</a>
    </div>

    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Affiliate</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Referred User</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Invoice</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Amount</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Date</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @forelse ($commissions as $commission)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $commission->member->user->email ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $commission->referral->referredUser->email ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                @if($commission->invoice)
                                    <a href="{{ route('admin.invoices.edit', $commission->invoice) }}" class="text-billmora-primary-500 hover:text-billmora-primary-600 font-semibold">{{ $commission->invoice->invoice_number }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $commission->currency }} {{ number_format($commission->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($commission->status === 'approved')
                                    <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                @elseif($commission->status === 'pending')
                                    <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                @else
                                    <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $commission->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                @if($commission->status === 'pending')
                                    <form action="{{ route('admin.modules.affiliate.commissions.approve', $commission) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-500 hover:text-green-600 font-semibold cursor-pointer">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.modules.affiliate.commissions.reject', $commission) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-red-400 hover:text-red-500 font-semibold cursor-pointer">Reject</button>
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
        {{ $commissions->links('admin::layouts.partials.pagination') }}
    </div>
</div>
@endsection
