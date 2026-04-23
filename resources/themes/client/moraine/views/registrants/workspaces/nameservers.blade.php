@extends('client::registrants.show')

@section('workspaces')
    <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
        <h2 class="text-xl font-bold text-slate-700 mb-4">{{ __('client/registrants.nameservers_label') }}</h2>
        <form action="{{ route('client.registrants.nameservers.update', $registrant->registrant_number) }}" method="POST" class="grid gap-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-client::input 
                    name="nameservers[]" 
                    label="Nameserver 1" 
                    value="{{ old('nameservers.0', $nameservers[0] ?? '') }}" 
                    placeholder="ns1.example.com" 
                    required 
                />
                <x-client::input 
                    name="nameservers[]" 
                    label="Nameserver 2" 
                    value="{{ old('nameservers.1', $nameservers[1] ?? '') }}" 
                    placeholder="ns2.example.com" 
                    required 
                />
                <x-client::input 
                    name="nameservers[]" 
                    label="Nameserver 3" 
                    value="{{ old('nameservers.2', $nameservers[2] ?? '') }}" 
                    placeholder="ns3.example.com" 
                />
                <x-client::input 
                    name="nameservers[]" 
                    label="Nameserver 4" 
                    value="{{ old('nameservers.3', $nameservers[3] ?? '') }}" 
                    placeholder="ns4.example.com" 
                />
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-2 text-white font-medium rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.submit') }}
                </button>
            </div>
        </form>
    </div>
@endsection
