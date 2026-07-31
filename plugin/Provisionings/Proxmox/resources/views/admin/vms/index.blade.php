@extends('provisioning.proxmox::admin.layout')

@section('title', 'Proxmox VM Overview')

@section('proxmox_content')
<div class="flex flex-col gap-4">
    <!-- Instance Selector & Refresh -->
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center w-full bg-white p-6 border-2 border-billmora-neutral-100 rounded-2xl">
        <div class="w-full md:w-1/3">
            <form action="{{ route('admin.provisionings.proxmox.index') }}" method="GET" class="flex gap-4 w-full">
                <x-admin::select name="instance_id" onchange="this.form.submit()">
                    @foreach($instances as $instance)
                        <option value="{{ $instance->id }}" @selected($selectedInstance->id == $instance->id)>
                            {{ $instance->name }}
                        </option>
                    @endforeach
                </x-admin::select>
            </form>
        </div>
        <div>
            <a href="{{ route('admin.provisionings.proxmox.index', ['instance_id' => $selectedInstance->id]) }}" class="flex gap-2 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2.5 text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer shadow-sm text-sm">
                <x-lucide-refresh-cw class="w-4 h-4" />
                Refresh Data
            </a>
        </div>
    </div>

    @if($error)
        <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200">
            <p class="font-semibold flex items-center gap-2"><x-lucide-alert-circle class="w-5 h-5"/> Error retrieving VMs from Proxmox:</p>
            <p class="text-sm mt-1">{{ $error }}</p>
        </div>
    @endif

    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-neutral-100 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-neutral-100">
                    <thead class="bg-billmora-neutral-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">VMID</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Hostname</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Node</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">IP Address</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Bandwidth</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Linked Service</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-neutral-100 bg-white">
                        @forelse ($vms as $vm)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-semibold">{{ $vm['vmid'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $vm['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($vm['status'] === 'running')
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Running
                                        </span>
                                    @elseif($vm['status'] === 'stopped')
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Stopped
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ ucfirst($vm['status']) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 flex items-center gap-2">
                                    <x-lucide-server class="w-4 h-4 text-slate-400" />
                                    {{ $vm['node'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    @if(empty($vm['ipv4s']) && empty($vm['ipv6s']))
                                        <span class="text-slate-400 italic">Unknown</span>
                                    @else
                                        <div class="flex flex-col gap-1">
                                            @foreach($vm['ipv4s'] as $ip)
                                                <span class="inline-flex w-max items-center py-0.5 px-2 rounded text-xs font-semibold font-mono bg-slate-100 text-slate-600 border border-slate-200">{{ $ip }}</span>
                                            @endforeach
                                            @foreach($vm['ipv6s'] as $ip)
                                                <span class="inline-flex w-max items-center py-0.5 px-2 rounded text-xs font-semibold font-mono bg-slate-100 text-slate-600 border border-slate-200">{{ explode('/', $ip)[0] }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    @php
                                        $conf = $vm['service'] ? ($vm['service']->configuration ?? []) : [];
                                        $netIn = $conf['bandwidth_usage_in'] ?? $vm['netin'] ?? 0;
                                        $netOut = $conf['bandwidth_usage_out'] ?? $vm['netout'] ?? 0;
                                        $usedBytes = $netIn + $netOut;
                                        $usedGb = round($usedBytes / (1024 ** 3), 2);
                                    @endphp
                                    {{ $usedGb }} GB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($vm['service'])
                                        <a href="{{ route('admin.services.edit', $vm['service']) }}" class="text-billmora-primary-500 hover:text-billmora-primary-600 font-medium hover:underline">
                                            #{{ $vm['service']->service_number }}
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                            Unmanaged
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($vm['status'] === 'running')
                                            <form action="{{ route('admin.provisionings.proxmox.vms.stop', $vm['vmid']) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="instance_id" value="{{ $selectedInstance->id }}">
                                                <input type="hidden" name="node" value="{{ $vm['node'] }}">
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 rounded-lg transition-colors text-xs font-semibold cursor-pointer" title="Force Stop">
                                                    <x-lucide-power-off class="w-3.5 h-3.5" /> Stop
                                                </button>
                                            </form>
                                        @elseif($vm['status'] === 'stopped')
                                            <form action="{{ route('admin.provisionings.proxmox.vms.start', $vm['vmid']) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="instance_id" value="{{ $selectedInstance->id }}">
                                                <input type="hidden" name="node" value="{{ $vm['node'] }}">
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 rounded-lg transition-colors text-xs font-semibold cursor-pointer" title="Power On">
                                                    <x-lucide-play class="w-3.5 h-3.5" /> Start
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <x-lucide-monitor class="w-12 h-12 mb-3 opacity-20" />
                                        <p class="text-slate-500 font-medium">No Virtual Machines found on this cluster.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
