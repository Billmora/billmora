@extends('admin::layouts.app')

@section('title', "Cancellation Edit - {$cancellation->id}")
    
@section('body')
<div class="flex flex-col-reverse lg:flex-row gap-5">
    <form id="cancelApproveForm" action="{{ route('admin.services.cancellations.approve', ['cancellation' => $cancellation->id]) }}" method="POST" class="hidden">
        @csrf
    </form>
    <form action="{{ route('admin.services.cancellations.reject', ['cancellation' => $cancellation->id]) }}" method="POST" class="w-full lg:w-2/3 h-fit grid gap-4">
        @csrf
        <div class="grid gap-4 bg-white p-8 border-billmora-2 rounded-2xl">
            @if ($cancellation->status !== 'pending')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin::input
                        name="cancellation_status"
                        label="{{ __('admin/services.cancellation_reviewed_by_label') }}"
                        value="{{ $cancellation->reviewedBy->fullname }}"
                        disabled
                        required
                    />
                    <x-admin::input
                        name="cancellation_status"
                        label="{{ __('admin/services.cancellation_reviewed_at_label') }}"
                        value="{{ $cancellation->reviewed_at->format(Billmora::getGeneral('company_date_format')) }} ({{ $cancellation->reviewed_at->format('g:i A') }})"
                        disabled
                        required
                    />
                </div>
            @endif
            <x-admin::textarea
                name="cancellation_rejection_note"
                label="{{ __('admin/services.cancellation_rejection_label') }}"
                rows="4"
                required
            >{{ old('cancellation_rejection_note', $cancellation->rejection_note) }}</x-admin::textarea>
        </div>
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.services.cancellations') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
            @if ($cancellation->status === 'pending')
                @can('services.cancellations.approve')
                    <button type="submit" form="cancelApproveForm" class="bg-green-500 hover:bg-green-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('admin/services.cancellation.approve') }}
                    </button>
                @endcan
                @can('services.cancellations.reject')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('admin/services.cancellation.reject') }}
                    </button>
                @endcan
            @endif
        </div>
    </form>
    <div class="w-full lg:w-1/3 h-fit grid gap-4 bg-white p-8 border-billmora-2 rounded-2xl">
        <div class="w-full">
            <label for="cancellation_service_id" class="flex text-slate-600 font-semibold mb-1">
                {{ __('admin/services.cancellation_service_label') }}
            </label>
            <a href="{{ route('admin.services.edit', ['service' => $cancellation->service->id]) }}" class="relative inline-block w-full group" target="_blank">
                <input type="text" name="cancellation_service_id" id="cancellation_service_id" value="{{ $cancellation->service->name }}" class="w-full px-3 py-2.25 bg-billmora-1 placeholder:text-gray-400 border-2 border-billmora-2 rounded-xl cursor-not-allowed" disabled>
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="button" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">
                        {{ __('admin/services.go_to_service') }}
                    </button>
                </div>
            </a>
        </div>
        <div class="w-full">
            <label for="cancellation_user_id" class="flex text-slate-600 font-semibold mb-1">
                {{ __('admin/services.user_label') }}
            </label>
            <a href="{{ route('admin.users.summary', ['id' => $cancellation->user->id]) }}" class="relative inline-block w-full group" target="_blank">
                <input type="text" name="cancellation_user_id" id="cancellation_user_id" value="{{ $cancellation->user->email }}" class="w-full px-3 py-2.25 bg-billmora-1 placeholder:text-gray-400 border-2 border-billmora-2 rounded-xl cursor-not-allowed" disabled>
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="button" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">
                        {{ __('admin/services.go_to_user') }}
                    </button>
                </div>
            </a>
        </div>
        <x-admin::input
            name="cancellation_status"
            label="{{ __('admin/services.status_label') }}"
            value="{{ ucwords($cancellation->status) }}"
            disabled
            required
        />
        @if ($cancellation->cancelled_at)
            <x-admin::input
                name="cancellation_cancelled_at"
                label="{{ __('admin/services.cancellation_cancelled_at_label') }}"
                value="{{ $cancellation->cancelled_at->format(Billmora::getGeneral('company_date_format')) }} ({{ $cancellation->cancelled_at->format('g:i A') }})"
                disabled
                required
            />
        @endif
        <x-admin::input
            name="cancellation_type"
            label="{{ __('admin/services.cancellation_type_label') }}"
            value="{{ ucwords(str_replace('_', ' ', $cancellation->type)) }}"
            disabled
            required
        />
        <x-admin::textarea
            name="cancellation_reason"
            label="{{ __('admin/services.cancellation_reason_label') }}"
            rows="6"
            disabled
            required
        >{{ $cancellation->reason }}</x-admin::textarea>
    </div>
</div>
@endsection