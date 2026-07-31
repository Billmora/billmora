@extends('admin::layouts.app')

@section('title', 'Add IPs to Pool: ' . $pool->name)

@section('body')
<div class="flex flex-col gap-4 w-full">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-slate-800">Add IPs to Pool: {{ $pool->name }}</h1>
    </div>

    <div class="w-full bg-white p-8 border-2 border-billmora-neutral-100 rounded-2xl">
        <form action="{{ route('admin.provisionings.proxmox.ip-pools.ips.store', $pool) }}" method="POST" class="flex flex-col gap-6" x-data="{ type: '{{ old('type', 'single') }}' }">
            @csrf

            <div x-on:change="type = $event.target.value">
                <x-admin::radio.group name="type" label="Addition Method">
                    <x-admin::radio.option name="type" label="Single IP" value="single" :checked="old('type', 'single') === 'single'" />
                    <x-admin::radio.option name="type" label="IP Range" value="range" :checked="old('type') === 'range'" />
                    <x-admin::radio.option name="type" label="CIDR Subnet" value="cidr" :checked="old('type') === 'cidr'" />
                </x-admin::radio.group>
            </div>

            <!-- Single IP -->
            <div x-show="type === 'single'" style="display: none;" class="flex flex-col gap-4">
                <x-admin::input type="text" name="ip_address" label="IP Address" placeholder="e.g. 192.168.1.50" value="{{ old('ip_address') }}" />
            </div>

            <!-- IP Range -->
            <div x-show="type === 'range'" style="display: none;" class="flex flex-col sm:flex-row gap-4">
                <div class="w-full">
                    <x-admin::input type="text" name="start_ip" label="Start IP" placeholder="e.g. 192.168.1.10" value="{{ old('start_ip') }}" />
                </div>
                <div class="w-full">
                    <x-admin::input type="text" name="end_ip" label="End IP" placeholder="e.g. 192.168.1.50" value="{{ old('end_ip') }}" />
                </div>
            </div>

            <!-- CIDR Subnet -->
            <div x-show="type === 'cidr'" style="display: none;" class="flex flex-col gap-4">
                <x-admin::input type="text" name="cidr" label="CIDR Subnet" placeholder="e.g. 192.168.1.0/24" value="{{ old('cidr') }}" />
                <p class="text-xs text-slate-500 mt-1">This will automatically add all usable IPs in the subnet (excluding network and broadcast addresses).</p>
            </div>

            <div class="flex justify-end gap-2 mt-2">
                <a href="{{ route('admin.provisionings.proxmox.ip-pools.ips.index', $pool) }}" class="bg-billmora-neutral-50 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors cursor-pointer text-sm font-semibold">Cancel</a>
                <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors cursor-pointer text-sm font-semibold border-2 border-transparent">Add IPs</button>
            </div>
        </form>
    </div>
</div>
@endsection
