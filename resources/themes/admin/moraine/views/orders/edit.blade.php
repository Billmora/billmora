@extends('admin::layouts.app')

@section('title', "Order Edit - {$order->order_number}")

@section('body')
<div class="grid gap-4">
    <div class="flex flex-col-reverse lg:flex-row gap-5">
        <form action="{{ route('admin.orders.update', ['order' => $order->id]) }}" method="POST" class="w-full lg:w-2/3 h-fit grid gap-4">
            @csrf
            @method('PATCH')
            <div class="grid gap-8 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
               <x-admin::select
                    name="order_status"
                    label="{{ __('admin/orders.status_label') }}"
                    helper="{{ __('admin/orders.status_helper') }}"
                    required
               >
                    <option value="pending" {{ old('order_status', $order->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ old('order_status', $order->status) === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ old('order_status', $order->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ old('order_status', $order->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="failed" {{ old('order_status', $order->status) === 'failed' ? 'selected' : '' }}>Failed</option>
               </x-admin::select>
            </div>
            <div class="flex gap-4 ml-auto">
                <a href="{{ route('admin.orders') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
                <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.update') }}
                </button>
            </div>
            <div class="w-full h-fit grid gap-6 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <div class="grid gap-4">
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/orders.items_label') }}</h3>
                    @foreach ($order->items as $item)
                        <div class="flex justify-between p-4 bg-white border-2 border-billmora-2 rounded-xl">
                            <div class="grid text-start">
                                <span class="text-billmora-primary font-bold">{{ $item->name }}</span>
                                <span class="text-slate-500 font-medium">x{{ $item->quantity }}</span>
                            </div>
                            <div class="grid text-end">
                                <span class="text-billmora-primary font-bold">{{ Currency::format($item->price, $order->currency) }}</span>
                                <span class="text-slate-500 font-medium">{{ $item->cycle_label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <hr class="border-t-2 border-billmora-2 border-dashed">
                <div class="grid gap-4">
                    <h3 class="text-lg font-bold text-slate-700">{{ __('admin/orders.services_label') }}</h3>
                    <div class="grid gap-2">
                        @foreach ($order->services as $service)
                            <div class="flex justify-between items-center p-3 bg-white border-2 border-billmora-2 rounded-lg">
                                <a href="{{ route('admin.services.edit', ['service' => $service->id]) }}" class="text-billmora-primary font-semibold hover:underline">
                                    {{ $service->service_number }}
                                </a>
                                <span class="px-2 py-1 text-sm text-slate-600 font-semibold rounded-md">
                                    {{ ucfirst($service->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
        <div class="w-full lg:w-1/3 h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl lg:sticky top-28 shrink-0">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('admin/orders.user_label') }}</span>
                <a href="{{ route('admin.users.summary', ['user' => $order->user->id]) }}" class="text-billmora-primary hover:text-billmora-primary-hover font-medium transition cursor-pointer">{{ $order->user->fullname }}</a>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('admin/orders.number_label') }}</span>
                <span class="text-slate-500 font-medium">#{{ $order->order_number }}</span>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('admin/orders.status_label') }}</span>
                <span class="text-slate-500 font-medium">{{ $order->status }}</span>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('admin/orders.currency_label') }}</span>
                <span class="text-slate-500 font-medium">{{ $order->currency }}</span>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('admin/orders.subtotal_label') }}</span>
                <span class="text-slate-500 font-medium">{{ Currency::format($order->subtotal, $order->currency) }}</span>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('admin/orders.discount_label') }}</span>
                <span class="text-slate-500 font-medium">{{ Currency::format($order->discount, $order->currency) }}</span>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('admin/orders.setup_fee_label') }}</span>
                <span class="text-slate-500 font-medium">{{ Currency::format($order->setup_fee, $order->currency) }}</span>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('admin/orders.total_label') }}</span>
                <span class="text-slate-500 font-medium">{{ Currency::format($order->total, $order->currency) }}</span>
            </div>
            @switch($order->status)
                @case('completed')
                    <hr class="border-t-2 border-billmora-2">
                    <div class="flex justify-between">
                        <span class="text-slate-600 font-semibold">{{ __('common.completed_at') }}</span>
                        <span class="text-slate-500 font-medium">{{ $order->completed_at->format(Billmora::getGeneral('company_date_format')) }}</span>
                    </div>
                    @break
                @case('cancelled')
                    <hr class="border-t-2 border-billmora-2">
                    <div class="flex justify-between">
                        <span class="text-slate-600 font-semibold">{{ __('common.cancelled_at') }}</span>
                        <span class="text-slate-500 font-medium">{{ $order->cancelled_at->format(Billmora::getGeneral('company_date_format')) }}</span>
                    </div>
                    @break
            @endswitch
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('common.created_at') }}</span>
                <span class="text-slate-500 font-medium">{{ $order->created_at->format(Billmora::getGeneral('company_date_format')) }}</span>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between">
                <span class="text-slate-600 font-semibold">{{ __('common.updated_at') }}</span>
                <span class="text-slate-500 font-medium">{{ $order->updated_at->format(Billmora::getGeneral('company_date_format')) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection