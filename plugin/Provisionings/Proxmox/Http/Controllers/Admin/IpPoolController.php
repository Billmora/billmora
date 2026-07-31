<?php

namespace Plugins\Provisionings\Proxmox\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Plugins\Provisionings\Proxmox\Models\ProxmoxIpPool;

class IpPoolController extends Controller
{
    use AuditsSystem;

    public function index(Request $request)
    {
        $search = $request->query('search');

        $pools = ProxmoxIpPool::with('instance')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('subnet', 'like', "%{$search}%");
            })
            ->withCount(['ips', 'ips as used_ips_count' => function ($query) {
                $query->where('status', 'used');
            }])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('provisioning.proxmox::admin.ip-pools.index', compact('pools', 'search'));
    }

    public function create()
    {
        $instances = Plugin::where('type', 'provisioning')
            ->where('provider', 'Proxmox')
            ->get();
            
        return view('provisioning.proxmox::admin.ip-pools.create', compact('instances'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plugin_id'     => ['required', 'exists:plugins,id'],
            'name'          => ['required', 'string', 'max:255'],
            'ip_version'    => ['required', 'in:ipv4,ipv6'],
            'subnet'        => ['nullable', 'string', 'max:255'],
            'gateway'       => ['nullable', 'string', 'max:255'],
            'netmask'       => ['nullable', 'string', 'max:255'],
            'prefix_length' => ['nullable', 'integer', 'min:0', 'max:128'],
            'nameservers'   => ['nullable', 'string'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $pool = ProxmoxIpPool::create([
            'plugin_id'     => $validated['plugin_id'],
            'name'          => $validated['name'],
            'ip_version'    => $validated['ip_version'],
            'subnet'        => $validated['subnet'],
            'gateway'       => $validated['gateway'],
            'netmask'       => $validated['netmask'],
            'prefix_length' => $validated['prefix_length'],
            'nameservers'   => $validated['nameservers'],
            'is_active'     => $validated['is_active'] ?? false,
        ]);

        $this->recordCreate('provisionings.proxmox.ip_pool.create', $pool->toArray());

        return redirect()->route('admin.provisionings.proxmox.ip-pools.index')
            ->with('success', __('common.create_success', ['attribute' => 'IP Pool']));
    }

    public function edit(ProxmoxIpPool $ip_pool)
    {
        $instances = Plugin::where('type', 'provisioning')
            ->where('provider', 'Proxmox')
            ->get();
            
        return view('provisioning.proxmox::admin.ip-pools.edit', compact('ip_pool', 'instances'));
    }

    public function update(Request $request, ProxmoxIpPool $ip_pool)
    {
        $validated = $request->validate([
            'plugin_id'     => ['required', 'exists:plugins,id'],
            'name'          => ['required', 'string', 'max:255'],
            'ip_version'    => ['required', 'in:ipv4,ipv6'],
            'subnet'        => ['nullable', 'string', 'max:255'],
            'gateway'       => ['nullable', 'string', 'max:255'],
            'netmask'       => ['nullable', 'string', 'max:255'],
            'prefix_length' => ['nullable', 'integer', 'min:0', 'max:128'],
            'nameservers'   => ['nullable', 'string'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $old = $ip_pool->getOriginal();

        $ip_pool->update([
            'plugin_id'     => $validated['plugin_id'],
            'name'          => $validated['name'],
            'ip_version'    => $validated['ip_version'],
            'subnet'        => $validated['subnet'],
            'gateway'       => $validated['gateway'],
            'netmask'       => $validated['netmask'],
            'prefix_length' => $validated['prefix_length'],
            'nameservers'   => $validated['nameservers'],
            'is_active'     => $validated['is_active'] ?? false,
        ]);

        $this->recordUpdate('provisionings.proxmox.ip_pool.update', $old, $ip_pool->getChanges());

        return redirect()->route('admin.provisionings.proxmox.ip-pools.index')
            ->with('success', __('common.save_success', ['attribute' => 'IP Pool']));
    }

    public function destroy(ProxmoxIpPool $ip_pool)
    {
        if ($ip_pool->ips()->where('status', 'used')->exists()) {
            return redirect()->back()->with('error', 'Cannot delete pool with used IPs.');
        }

        $this->recordDelete('provisionings.proxmox.ip_pool.delete', $ip_pool->toArray());
        
        $ip_pool->delete();

        return redirect()->route('admin.provisionings.proxmox.ip-pools.index')
            ->with('success', __('common.delete_success', ['attribute' => 'IP Pool']));
    }
}
