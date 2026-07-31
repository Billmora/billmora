@extends('provisioning.proxmox::admin.layout')

@section('title', 'Create IP Pool')

@section('proxmox_content')
<div class="flex flex-col gap-4 w-full">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-slate-800">Create IP Pool</h1>
    </div>

    <div class="w-full bg-white p-8 border-2 border-billmora-neutral-100 rounded-2xl">
        <form action="{{ route('admin.provisionings.proxmox.ip-pools.store') }}" method="POST" class="flex flex-col gap-6">
            @csrf

            <x-admin::select name="plugin_id" label="Proxmox Instance" required="true">
                @foreach($instances as $instance)
                    <option value="{{ $instance->id }}" {{ old('plugin_id') == $instance->id ? 'selected' : '' }}>{{ $instance->name }}</option>
                @endforeach
            </x-admin::select>

            <x-admin::select name="ip_version" label="IP Version" required="true">
                <option value="ipv4" {{ old('ip_version') == 'ipv4' ? 'selected' : '' }}>IPv4</option>
                <option value="ipv6" {{ old('ip_version') == 'ipv6' ? 'selected' : '' }}>IPv6</option>
            </x-admin::select>

            <x-admin::input type="text" name="name" label="Pool Name" placeholder="e.g. Main Datacenter IPv4" value="{{ old('name') }}" required="true" />
            
            <x-admin::input type="text" name="subnet" label="Subnet" placeholder="e.g. 192.168.1.0/24 or 2001:db8::/64" value="{{ old('subnet') }}" />
            
            <x-admin::input type="text" name="gateway" label="Gateway" placeholder="e.g. 192.168.1.1 or 2001:db8::1" value="{{ old('gateway') }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-admin::input type="text" name="netmask" label="Netmask (IPv4)" placeholder="e.g. 255.255.255.0 or 24" value="{{ old('netmask') }}" />
                <x-admin::input type="number" name="prefix_length" label="Prefix Length (IPv6)" placeholder="e.g. 64" value="{{ old('prefix_length') }}" />
            </div>
            
            <x-admin::textarea name="nameservers" label="Nameservers" placeholder="e.g. 8.8.8.8, 1.1.1.1" value="{{ old('nameservers') }}" />

            <x-admin::toggle name="is_active" label="Active" :checked="old('is_active', true)" />

            <div class="flex justify-end gap-2 mt-2">
                <a href="{{ route('admin.provisionings.proxmox.ip-pools.index') }}" class="bg-billmora-neutral-50 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors cursor-pointer text-sm font-semibold">Cancel</a>
                <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors cursor-pointer text-sm font-semibold border-2 border-transparent">Create Pool</button>
            </div>
        </form>
    </div>
</div>
@endsection
