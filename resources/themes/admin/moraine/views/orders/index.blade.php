@extends('admin::layouts.app')

@section('title', 'Manage Orders')

@section('body')
<div class="flex flex-col gap-4">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-auto flex gap-2 flex-1 max-w-150">
            <form action="{{ route('admin.orders') }}" method="GET" class="relative inline-block w-full group m-0">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                </div>
                @foreach(request()->only(['sort', 'direction']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                @foreach(request()->query() as $key => $value)
                    @if(str_starts_with($key, 'filter_') && !is_null($value) && $value !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <input type="text" name="search" id="search" placeholder="{{ __('admin/common.search') }}" value="{{ request('search') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-neutral-100 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
            
            <x-admin::drawer.trigger drawer="ordersFilter" type="button" class="relative flex items-center justify-center bg-white border-2 border-billmora-neutral-100 hover:border-billmora-primary-500 text-slate-600 px-3 py-2 rounded-xl transition duration-300 cursor-pointer shrink-0">
                <x-lucide-filter class="w-5 h-auto" />
                @if(collect($filters ?? [])->filter()->isNotEmpty())
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                @endif
            </x-admin::drawer.trigger>
        </div>
        @can('orders.create')
            <a href="{{ route('admin.orders.create') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-plus class="w-auto h-5" />
                {{ __('common.create') }}
            </a>
        @endcan
    </div>
    
    {{-- Active Filters --}}
    @if(collect($filters ?? [])->filter()->isNotEmpty())
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm text-slate-500 font-medium mr-1">{{ __('common.filter') ?? 'Filters' }}:</span>
            
            @if(!empty($filters['order_number']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_order_number') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('admin/orders.number_label') }}: {{ $filters['order_number'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif

            @if(!empty($filters['status']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_status') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('common.status') }}: {{ ucfirst($filters['status']) }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            @if(!empty($filters['currency']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_currency') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('common.currency') }}: {{ $filters['currency'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            @if(!empty($filters['date_from']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_date_from') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('common.created_at') }} ({{ __('common.date_from') ?? 'From' }}): {{ $filters['date_from'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            @if(!empty($filters['date_to']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_date_to') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('common.created_at') }} ({{ __('common.date_to') ?? 'To' }}): {{ $filters['date_to'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif

            @if(!empty($filters['total_min']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_total_min') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('common.total') }} ({{ __('common.min') }}): {{ $filters['total_min'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif

            @if(!empty($filters['total_max']))
                <a href="{{ request()->fullUrlWithoutQuery('filter_total_max') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                    {{ __('common.total') }} ({{ __('common.max') }}): {{ $filters['total_max'] }}
                    <x-lucide-x class="w-3.5 h-3.5" />
                </a>
            @endif
            
            <a href="{{ request()->url() }}" class="text-sm font-medium text-red-500 hover:text-red-600 ml-2">{{ __('common.clear_all') ?? 'Clear All' }}</a>
        </div>
    @endif
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-neutral-100 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-neutral-100">
                    <thead class="bg-billmora-neutral-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">ID</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/orders.number_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/orders.user_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/orders.date_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/orders.total_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-neutral-100 bg-white">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $order->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    <a href="{{ route('admin.orders.edit', ['order' => $order->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $order->user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $order->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ Currency::format($order->total, $order->currency) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $order->status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    @can('orders.update')
                                        <a href="{{ route('admin.orders.edit', ['order' => $order->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">{{ __('common.edit') }}</a>                               
                                    @endcan
                                    @can('orders.delete')
                                        <x-admin::modal.trigger modal="deleteModal-{{ $order->order_number }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-400">{{ __('common.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        {{ $orders->links('admin::layouts.partials.pagination') }}
    </div>
    @can('orders.delete')
        @foreach ($orders as $order)
            <x-admin::modal.content
                modal="deleteModal-{{ $order->order_number }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.delete_modal_title') }}"
                description="{{ __('common.delete_modal_description', ['item' => $order->order_number]) }}">
                <form action="{{ route('admin.orders.destroy', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-neutral-50 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                    </div>
                </form>
            </x-admin::modal.content>
        @endforeach
    @endcan
</div>

<x-admin::drawer.content drawer="ordersFilter" title="{{ __('common.filter_title', ['resource' => __('admin/navigation.orders')]) }}" action="{{ request()->url() }}">
    @if(request()->has('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
    @endif

    <x-admin::select name="filter_status" label="{{ __('admin/orders.filter.status_label') }}">
        <option value="pending" @selected(request('filter_status') === 'pending')>{{ __('admin/orders.filter.status_pending') }}</option>
        <option value="active" @selected(request('filter_status') === 'active')>{{ __('admin/orders.filter.status_active') }}</option>
        <option value="cancelled" @selected(request('filter_status') === 'cancelled')>{{ __('admin/orders.filter.status_cancelled') }}</option>
        <option value="fraud" @selected(request('filter_status') === 'fraud')>{{ __('admin/orders.filter.status_fraud') }}</option>
    </x-admin::select>

    <x-admin::select name="filter_currency" label="{{ __('common.currency') }}">
        @foreach($currencies as $currency)
            <option value="{{ $currency->code }}" @selected(request('filter_currency') === $currency->code)>{{ $currency->code }}</option>
        @endforeach
    </x-admin::select>

    <div class="grid grid-cols-2 gap-4">
        <x-admin::input name="filter_total_min" type="number"
            label="{{ __('common.total') }} ({{ __('common.min') }})"
            value="{{ request('filter_total_min') }}" step="0.01" />
        <x-admin::input name="filter_total_max" type="number"
            label="{{ __('common.total') }} ({{ __('common.max') }})"
            value="{{ request('filter_total_max') }}" step="0.01" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-admin::input name="filter_date_from" type="date"
            label="{{ __('common.created_at') }} ({{ __('common.date_from') }})"
            value="{{ request('filter_date_from') }}" />
        <x-admin::input name="filter_date_to" type="date"
            label="{{ __('common.created_at') }} ({{ __('common.date_to') }})"
            value="{{ request('filter_date_to') }}" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-admin::input name="filter_updated_at_from" type="date"
            label="{{ __('common.updated_at') }} ({{ __('common.date_from') }})"
            value="{{ request('filter_updated_at_from') }}" />
        <x-admin::input name="filter_updated_at_to" type="date"
            label="{{ __('common.updated_at') }} ({{ __('common.date_to') }})"
            value="{{ request('filter_updated_at_to') }}" />
    </div>
</x-admin::drawer.content>

@endsection