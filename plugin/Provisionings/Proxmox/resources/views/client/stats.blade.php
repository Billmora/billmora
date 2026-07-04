@extends('client::services.show')

@section('workspaces')
@php
    $cores          = (int) ($config['cores'] ?? 1);
    $memory         = (int) ($config['memory'] ?? 1024);
    $disk           = (int) ($config['disk_size'] ?? 20);
    $bandwidthLimit = (int) ($config['bandwidth'] ?? 0);

    $netIn        = $stats['netin'] ?? 0;
    $netOut       = $stats['netout'] ?? 0;
    $usedBytes    = $netIn + $netOut;
    $usedGb       = round($usedBytes / (1024 ** 3), 2);
    $limitLabel   = $bandwidthLimit > 0 ? $bandwidthLimit . ' GB' : 'Unmetered';
    $pct          = ($bandwidthLimit > 0 && $usedGb > 0) ? min(100, round(($usedGb / $bandwidthLimit) * 100)) : 0;
    $memoryLabel  = $memory >= 1024 ? round($memory / 1024, 1) . ' GB' : $memory . ' MB';

    $ipv4List = collect($ipAddresses ?? [])->where('type', 'IPV4')->pluck('ip');
    $ipv6List = collect($ipAddresses ?? [])->where('type', 'IPV6')->pluck('ip');
    $agentRunning = !empty($ipAddresses);
@endphp

<div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl overflow-hidden">
    <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2 flex items-center gap-2">
        <i class="fa-solid fa-circle-info text-billmora-primary-500"></i>
        <h3 class="font-semibold text-slate-600">Summary</h3>
    </div>

    <div class="divide-y divide-billmora-2">

        {{-- IPv4 --}}
        <div class="flex items-start justify-between px-6 py-4">
            <span class="text-sm text-slate-500">IPv4 Address</span>
            <div class="text-right">
                @if($ipv4List->isNotEmpty())
                    @foreach($ipv4List as $ip)
                        <p class="text-sm font-semibold text-slate-700 font-mono">{{ $ip }}</p>
                    @endforeach
                @else
                    <p class="text-sm text-slate-400 italic">
                        {{ $agentRunning ? 'No IPv4 found' : 'Install Guest Agent to display' }}
                    </p>
                @endif
            </div>
        </div>

        {{-- IPv6 --}}
        <div class="flex items-start justify-between px-6 py-4">
            <span class="text-sm text-slate-500">IPv6 Address</span>
            <div class="text-right">
                @if($ipv6List->isNotEmpty())
                    @foreach($ipv6List as $ip)
                        <p class="text-sm font-semibold text-slate-700 font-mono">{{ $ip }}</p>
                    @endforeach
                @else
                    <p class="text-sm text-slate-400 italic">
                        {{ $agentRunning ? 'No IPv6 found' : 'Install Guest Agent to display' }}
                    </p>
                @endif
            </div>
        </div>

        {{-- CPU --}}
        <div class="flex items-center justify-between px-6 py-4">
            <span class="text-sm text-slate-500">CPU Cores</span>
            <span class="text-sm font-semibold text-slate-700">{{ $cores }} vCPU</span>
        </div>

        {{-- Memory --}}
        <div class="flex items-center justify-between px-6 py-4">
            <span class="text-sm text-slate-500">Memory</span>
            <span class="text-sm font-semibold text-slate-700">{{ $memoryLabel }}</span>
        </div>

        {{-- Disk --}}
        <div class="flex items-center justify-between px-6 py-4">
            <span class="text-sm text-slate-500">Disk Space</span>
            <span class="text-sm font-semibold text-slate-700">{{ $disk }} GB</span>
        </div>

        {{-- Bandwidth --}}
        <div class="px-6 py-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-slate-500">Bandwidth</span>
                <span class="text-sm font-semibold text-slate-700">{{ $usedGb }} GB / {{ $limitLabel }}</span>
            </div>
            @if($bandwidthLimit > 0)
            <div class="w-full bg-billmora-2 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $pct > 90 ? 'bg-red-500' : ($pct > 75 ? 'bg-yellow-400' : 'bg-billmora-primary-500') }}"
                     style="width: {{ $pct }}%"></div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
