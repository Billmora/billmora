@extends('provisioning.proxmox::admin.layout')

@section('title', 'Proxmox Node Monitor')

@section('proxmox_content')
<div class="flex flex-col gap-4">
    <!-- Instance Selector & Refresh -->
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center w-full bg-white p-6 border-2 border-billmora-neutral-100 rounded-2xl">
        <div class="w-full md:w-1/3">
            <form action="{{ route('admin.provisionings.proxmox.nodes.index') }}" method="GET" class="flex gap-4 w-full">
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
            <a href="{{ route('admin.provisionings.proxmox.nodes.index', ['instance_id' => $selectedInstance->id]) }}" class="flex gap-2 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2.5 text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer shadow-sm text-sm">
                <x-lucide-refresh-cw class="w-4 h-4" />
                Refresh Data
            </a>
        </div>
    </div>

    @if($error)
        <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200">
            <p class="font-semibold flex items-center gap-2"><x-lucide-alert-circle class="w-5 h-5"/> Error retrieving nodes from Proxmox:</p>
            <p class="text-sm mt-1">{{ $error }}</p>
        </div>
    @endif

    @if(!$error && count($nodes) === 0)
        <div class="bg-white p-8 text-center border-2 border-billmora-neutral-100 rounded-2xl">
            <x-lucide-server class="w-12 h-12 text-slate-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-slate-700">No nodes found</h3>
            <p class="text-slate-500 mt-1">This Proxmox cluster has no active nodes, or the API user lacks permissions.</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($nodes as $node)
            <div class="bg-white border-2 border-billmora-neutral-100 rounded-2xl overflow-hidden flex flex-col relative {{ $node['status'] === 'online' ? '' : 'opacity-75' }}">
                <div class="p-5 border-b-2 border-billmora-neutral-100 bg-slate-50/50 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white rounded-lg border border-billmora-neutral-100 shadow-sm">
                            <x-lucide-server class="w-5 h-5 text-slate-600" />
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg">{{ $node['node'] }}</h3>
                    </div>
                    @if($node['status'] === 'online')
                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Online
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Offline
                        </span>
                    @endif
                </div>

                <div class="p-5 flex-1 flex flex-col gap-6">
                    @if($node['status'] === 'online')
                        @php
                            $cpuPct = round(($node['cpu'] ?? 0) * 100, 1);
                            $memUsedGb = round(($node['mem'] ?? 0) / (1024 * 1024 * 1024), 2);
                            $memTotalGb = round(($node['maxmem'] ?? 0) / (1024 * 1024 * 1024), 2);
                            $memPct = $memTotalGb > 0 ? round(($memUsedGb / $memTotalGb) * 100, 1) : 0;
                            
                            $diskUsedGb = round(($node['storage_usage'] ?? 0) / (1024 * 1024 * 1024), 2);
                            $diskTotalGb = round(($node['storage_total'] ?? 0) / (1024 * 1024 * 1024), 2);
                            $diskPct = $diskTotalGb > 0 ? round(($diskUsedGb / $diskTotalGb) * 100, 1) : 0;
                            
                            $uptime = isset($node['uptime']) ? \Carbon\CarbonInterval::seconds($node['uptime'])->cascade()->forHumans(['short' => true]) : 'Unknown';
                        @endphp

                        <!-- CPU -->
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-medium text-slate-600 flex items-center gap-1.5"><x-lucide-cpu class="w-4 h-4" /> CPU Usage</span>
                                <span class="font-bold text-slate-800">{{ $cpuPct }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-blue-500 h-2 rounded-full transition-all" style="width: {{ min($cpuPct, 100) }}%"></div>
                            </div>
                        </div>

                        <!-- RAM -->
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-medium text-slate-600 flex items-center gap-1.5"><x-lucide-memory-stick class="w-4 h-4" /> RAM Usage</span>
                                <span class="font-bold text-slate-800">{{ $memUsedGb }} / {{ $memTotalGb }} GB ({{ $memPct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ min($memPct, 100) }}%"></div>
                            </div>
                        </div>

                        <!-- Storage -->
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-medium text-slate-600 flex items-center gap-1.5"><x-lucide-hard-drive class="w-4 h-4" /> Storage Usage</span>
                                <span class="font-bold text-slate-800">{{ $diskUsedGb }} / {{ $diskTotalGb }} GB ({{ $diskPct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-purple-500 h-2 rounded-full transition-all" style="width: {{ min($diskPct, 100) }}%"></div>
                            </div>
                        </div>

                        <!-- Stats Footer -->
                        <div class="mt-auto pt-4 border-t-2 border-slate-50 flex justify-between items-center">
                            <div class="flex flex-col">
                                <span class="text-xs text-slate-500 uppercase font-semibold">Active VMs</span>
                                <span class="text-lg font-bold text-slate-800">{{ $node['qemu_running'] }} <span class="text-sm font-medium text-slate-400">/ {{ $node['qemu_total'] }}</span></span>
                            </div>
                            <div class="flex flex-col text-right">
                                <span class="text-xs text-slate-500 uppercase font-semibold">Uptime</span>
                                <span class="text-sm font-bold text-slate-800 mt-1">{{ $uptime }}</span>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-slate-400 py-8">
                            <x-lucide-cloud-off class="w-10 h-10 mb-2 opacity-50" />
                            <p class="text-sm font-medium">Node is unreachable</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
