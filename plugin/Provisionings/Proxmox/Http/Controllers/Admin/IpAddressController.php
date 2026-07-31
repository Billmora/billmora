<?php

namespace Plugins\Provisionings\Proxmox\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Plugins\Provisionings\Proxmox\Models\ProxmoxIpPool;
use Plugins\Provisionings\Proxmox\Models\ProxmoxIpAddress;

class IpAddressController extends Controller
{
    use AuditsSystem;

    public function index(Request $request, ProxmoxIpPool $pool)
    {
        $search = $request->query('search');

        $ips = $pool->ips()
            ->with('service')
            ->when($search, function ($query, $search) {
                $query->where('ip_address', 'like', "%{$search}%");
            })
            ->orderBy('ip_address')
            ->paginate(50)
            ->withQueryString();

        return view('provisioning.proxmox::admin.ip-addresses.index', compact('pool', 'ips', 'search'));
    }

    public function create(ProxmoxIpPool $pool)
    {
        return view('provisioning.proxmox::admin.ip-addresses.create', compact('pool'));
    }

    public function store(Request $request, ProxmoxIpPool $pool)
    {
        $validated = $request->validate([
            'type'       => ['required', 'in:single,range,cidr'],
            'ip_address' => ['required_if:type,single', 'nullable', 'ipv4'],
            'start_ip'   => ['required_if:type,range', 'nullable', 'ipv4'],
            'end_ip'     => ['required_if:type,range', 'nullable', 'ipv4'],
            'cidr'       => ['required_if:type,cidr', 'nullable', 'string', 'regex:/^([0-9]{1,3}\.){3}[0-9]{1,3}(\/([0-9]|[1-2][0-9]|3[0-2]))?$/']
        ]);

        $addedCount = 0;

        if ($validated['type'] === 'single') {
            ProxmoxIpAddress::firstOrCreate([
                'ip_pool_id' => $pool->id,
                'ip_address' => $validated['ip_address']
            ]);
            $addedCount = 1;
        } elseif ($validated['type'] === 'range') {
            $start = ip2long($validated['start_ip']);
            $end = ip2long($validated['end_ip']);
            
            if ($start > $end) {
                return redirect()->back()->withInput()->with('error', 'Start IP must be less than or equal to End IP.');
            }

            for ($i = $start; $i <= $end; $i++) {
                ProxmoxIpAddress::firstOrCreate([
                    'ip_pool_id' => $pool->id,
                    'ip_address' => long2ip($i)
                ]);
                $addedCount++;
            }
        } elseif ($validated['type'] === 'cidr') {
            list($subnet, $mask) = explode('/', $validated['cidr']);
            $ipLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - $mask)) - 1);
            $network = $ipLong & $maskLong;
            $broadcast = $network | (~$maskLong);
            
            // Skip network (.0) and broadcast (.255) addresses typically
            for ($i = $network + 1; $i < $broadcast; $i++) {
                ProxmoxIpAddress::firstOrCreate([
                    'ip_pool_id' => $pool->id,
                    'ip_address' => long2ip($i)
                ]);
                $addedCount++;
            }
        }

        $this->recordCreate('provisionings.proxmox.ip_address.create', [
            'pool_id' => $pool->id,
            'type' => $validated['type'],
            'added' => $addedCount
        ]);

        return redirect()->route('admin.provisionings.proxmox.ip-pools.ips.index', $pool)
            ->with('success', "Successfully added {$addedCount} IP addresses to the pool.");
    }

    public function destroy(ProxmoxIpPool $pool, ProxmoxIpAddress $ip)
    {
        if ($ip->status === 'used') {
            return redirect()->back()->with('error', 'Cannot delete an IP address that is currently in use.');
        }

        $this->recordDelete('provisionings.proxmox.ip_address.delete', $ip->toArray());
        
        $ip->delete();

        return redirect()->route('admin.provisionings.proxmox.ip-pools.ips.index', $pool)
            ->with('success', __('common.delete_success', ['attribute' => 'IP Address']));
    }
}
