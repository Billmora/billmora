@extends('client::services.show')

@section('workspaces')
<form action="{{ route('client.services.cancellation.store', ['service' => $service->id]) }}" method="POST" class="bg-white border-2 border-billmora-2 rounded-2xl overflow-hidden">
    @csrf
    <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
        <h3 class="flex gap-2 items-center font-semibold text-slate-600">
            <x-lucide-ban class="w-auto h-5" />
            {{ __('client/services.cancel_label') }}
        </h3>
    </div>
    <div class="grid gap-4 p-6">
        <x-client::select
            name="cancellation_type"
            label="{{ __('client/services.cancellation.type_label') }}"
            required
        >
            @foreach (['immediate', 'end_of_period'] as $type)
                <option value="{{ $type }}" {{ old('cancellation_type', $type) === $type ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $type)) }}
                </option>
            @endforeach
        </x-client::select>
        <x-client::textarea
            name="cancellation_reason"
            label="{{ __('client/services.cancellation.reason_label') }}"
            rows="4"
            required
        >{{ old('cancellation_reason') }}</x-client::textarea>
        <div class="flex justify-end">
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-6 py-2 text-white font-medium rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.submit') }}
            </button>
        </div>
    </div>
</form>
@endsection