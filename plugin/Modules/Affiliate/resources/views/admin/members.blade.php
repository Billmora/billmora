@extends('admin::layouts.app')

@section('title', 'Affiliate Members')

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

    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-100">
            <form action="{{ route('admin.modules.affiliate.members') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                </div>
                <input type="text" name="search" placeholder="{{ __('admin/common.search') }}" value="{{ request('search') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">User</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Referral Code</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Referrals</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Balance</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Total Earned</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @forelse ($members as $member)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                <a href="{{ route('admin.users.summary', $member->user_id) }}" class="text-billmora-primary-500 hover:text-billmora-primary-600 font-semibold">{{ $member->user->email }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-mono">{{ $member->referral_code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $member->referrals_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($member->balance, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($member->total_earned, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($member->status === 'active')
                                    <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex items-center py-1 px-2 rounded-md text-xs font-medium bg-red-100 text-red-800">Suspended</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                @if($member->status === 'active')
                                    <form action="{{ route('admin.modules.affiliate.members.suspend', $member) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-red-400 hover:text-red-500 font-semibold cursor-pointer">Suspend</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.modules.affiliate.members.activate', $member) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-500 hover:text-green-600 font-semibold cursor-pointer">Activate</button>
                                    </form>
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
        {{ $members->links('admin::layouts.partials.pagination') }}
    </div>
</div>
@endsection
