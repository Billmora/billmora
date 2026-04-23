@extends('client::registrants.show')

@section('workspaces')
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl overflow-hidden p-8">
        <h2 class="text-xl font-bold text-slate-700 mb-4">{{ __('client/registrants.auto_renew_label') }}</h2>
        <form action="{{ route('client.registrants.autorenew.update', $registrant->registrant_number) }}" method="POST"
            class="grid gap-6 w-full">
            @csrf
            @method('PUT')

            <x-client::toggle name="auto_renew" label="{{ __('client/registrants.auto_renew_label') }}"
                :checked="$registrant->auto_renew" />

            <div class="flex justify-end mt-2">
                <button type="submit"
                    class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-2 text-white font-medium rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.submit') }}
                </button>
            </div>
        </form>
    </div>
@endsection