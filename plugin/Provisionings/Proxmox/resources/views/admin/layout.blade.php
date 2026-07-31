@extends('admin::layouts.app')

@section('body')
<div class="flex flex-col gap-6">
    <div class="flex items-center gap-3">
        <div class="bg-billmora-primary-50 text-billmora-primary-500 w-12 h-12 rounded-xl flex items-center justify-center">
            <x-lucide-server class="w-6 h-6" />
        </div>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Proxmox Management</h1>
            <p class="text-slate-500 text-sm">Manage your Proxmox virtual machines, nodes, and IP pools.</p>
        </div>
    </div>

    <x-admin::tabs :tabs="[
        [
            'route' => route('admin.provisionings.proxmox.index'),
            'icon' => 'lucide-monitor',
            'label' => 'VM Overview',
        ],
        [
            'route' => route('admin.provisionings.proxmox.nodes.index'),
            'icon' => 'lucide-server',
            'label' => 'Node Monitor',
        ],
        [
            'route' => route('admin.provisionings.proxmox.ip-pools.index'),
            'icon' => 'lucide-network',
            'label' => 'IP Pools',
        ],
    ]" active="{{ request()->routeIs('admin.provisionings.proxmox.ip-pools.*') ? route('admin.provisionings.proxmox.ip-pools.index') : (request()->routeIs('admin.provisionings.proxmox.nodes.*') ? route('admin.provisionings.proxmox.nodes.index') : route('admin.provisionings.proxmox.index')) }}" />

    <div>
        @yield('proxmox_content')
    </div>
</div>
@endsection
