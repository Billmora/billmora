@extends('admin::layouts.app')

@section('title', "User Services - {$user->email}")

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
                'route' => route('admin.users.services', ['user' => $user->id]),
                'icon' => 'lucide-scan-text',
                'label' => __('admin/users.tabs.services'),
            ],
            [
                'route' => route('admin.users.invoices', ['user' => $user->id]),
                'icon' => 'lucide-receipt-text',
                'label' => __('admin/users.tabs.invoices'),
            ],
            [
                'route' => route('admin.users.credits', ['user' => $user->id]),
                'icon' => 'lucide-badge-cent',
                'label' => __('admin/users.tabs.credits'),
            ],
            [
                'route' => route('admin.users.tickets', ['user' => $user->id]),
                'icon' => 'lucide-ticket',
                'label' => __('admin/users.tabs.tickets'),
            ],
            [
                'route' => route('admin.users.activity', ['user' => $user->id]),
                'icon' => 'lucide-activity',
                'label' => __('admin/users.tabs.activity'),
            ],
        ]" 
        active="{{ request()->url() }}"
    />
    <div class="w-full md:w-100">
        <form action="{{ route('admin.users.invoices', ['user' => $user->id]) }}" method="GET" class="relative inline-block max-w-150 w-full group">
            <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
            </div>
            <input type="text" name="search" id="search" placeholder="{{ __('admin/common.search') }}" value="{{ request('search') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
            <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
            </div>
        </form>
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">ID</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/invoices.number_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/invoices.date_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/invoices.total_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $invoice->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    <a href="{{ route('admin.invoices.edit', ['invoice' => $invoice->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">{{ $invoice->invoice_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $invoice->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ Currency::format($invoice->total, $invoice->currency) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $invoice->status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    @can('invoices.update')
                                        <a href="{{ route('admin.invoices.edit', ['invoice' => $invoice->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">{{ __('common.edit') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-400">{{ __('common.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        {{ $invoices->links('admin::layouts.partials.pagination') }}
    </div>
</div>
@endsection