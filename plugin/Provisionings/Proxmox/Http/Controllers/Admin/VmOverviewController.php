<?php

namespace Plugins\Provisionings\Proxmox\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Models\Service;

class VmOverviewController extends Controller
{
    public function index(Request $request)
    {
        $instances = Plugin::where('type', 'provisioning')
            ->where('provider', 'Proxmox')
            ->where('is_active', true)
            ->get();

        if ($instances->isEmpty()) {
            return redirect()->route('admin.provisionings')->with('error', 'No active Proxmox instances found.');
        }

        $selectedInstanceId = $request->query('instance_id', $instances->first()->id);
        $selectedInstance = $instances->firstWhere('id', $selectedInstanceId) ?? $instances->first();

        /** @var \Plugins\Provisionings\Proxmox\ProxmoxProvisioning $pluginClass */
        $pluginClass = new \Plugins\Provisionings\Proxmox\ProxmoxProvisioning(app());
        $pluginClass->setPluginModel($selectedInstance);
        $pluginClass->setInstanceConfig($selectedInstance->config ?? []);

        $vms = [];
        $error = null;

        try {
            // First get all nodes
            $response = $pluginClass->getHttpClient()->get($pluginClass->getApiUrl('/nodes'));
            if ($response->successful()) {
                $nodes = $response->json('data', []);
                
                // For each node, get VMs
                foreach ($nodes as $node) {
                    if ($node['status'] !== 'online') continue;
                    
                    $nodeName = $node['node'];
                    $qemuResp = $pluginClass->getHttpClient()->get($pluginClass->getApiUrl("/nodes/{$nodeName}/qemu"));
                    
                    if ($qemuResp->successful()) {
                        $nodeVms = $qemuResp->json('data', []);
                        foreach ($nodeVms as $vm) {
                            $vms[$vm['vmid']] = [
                                'vmid' => $vm['vmid'],
                                'name' => $vm['name'] ?? 'Unknown',
                                'status' => $vm['status'],
                                'node' => $nodeName,
                                'service' => null,
                                'netin' => $vm['netin'] ?? 0,
                                'netout' => $vm['netout'] ?? 0,
                                'ipv4s' => [],
                                'ipv6s' => [],
                            ];
                        }
                    }
                }
            } else {
                $error = 'Failed to connect to Proxmox API: HTTP ' . $response->status();
            }
        } catch (\Exception $e) {
            $error = 'Connection error: ' . $e->getMessage();
        }

        // Match with Billmora Services
        $services = Service::where('plugin_id', $selectedInstance->id)->get();
        foreach ($services as $service) {
            $config = $service->configuration ?? [];
            if (isset($config['proxmox_vmid'])) {
                $vmid = (int) $config['proxmox_vmid'];
                if (isset($vms[$vmid])) {
                    $vms[$vmid]['service'] = $service;

                    // Use cached guest IPs from service config (populated by cron job)
                    if (!empty($config['guest_ipv4'])) {
                        $vms[$vmid]['ipv4s'] = array_filter(explode(', ', $config['guest_ipv4']));
                    }
                    if (!empty($config['guest_ipv6'])) {
                        $vms[$vmid]['ipv6s'] = array_filter(explode(', ', $config['guest_ipv6']));
                    }
                    // Fallback: if no guest IPs cached yet, use allocated_ip from provisioning
                    if (empty($vms[$vmid]['ipv4s']) && !empty($config['allocated_ip'])) {
                        $vms[$vmid]['ipv4s'] = [$config['allocated_ip']];
                    }
                    if (empty($vms[$vmid]['ipv6s']) && !empty($config['allocated_ipv6'])) {
                        $vms[$vmid]['ipv6s'] = [$config['allocated_ipv6']];
                    }
                }
            }
        }

        // For running VMs with no IPs yet, try Guest Agent directly
        foreach ($vms as &$vm) {
            if ($vm['status'] === 'running' && empty($vm['ipv4s']) && empty($vm['ipv6s'])) {
                try {
                    $agentResp = $pluginClass->getHttpClient()->get(
                        $pluginClass->getApiUrl("/nodes/{$vm['node']}/qemu/{$vm['vmid']}/agent/network-get-interfaces")
                    );
                    if ($agentResp->successful()) {
                        foreach ($agentResp->json('data.result', []) as $iface) {
                            if (($iface['name'] ?? '') === 'lo') continue;
                            foreach ($iface['ip-addresses'] ?? [] as $addr) {
                                $ip   = $addr['ip-address'] ?? '';
                                $type = $addr['ip-address-type'] ?? '';
                                if ($ip && $type === 'ipv4') $vm['ipv4s'][] = $ip;
                                if ($ip && $type === 'ipv6') $vm['ipv6s'][] = $ip;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Guest Agent not running, skip silently.
                }
            }
        }
        unset($vm);

        // Convert to collection and sort
        $vms = collect($vms)->sortBy('vmid')->values();

        return view('provisioning.proxmox::admin.vms.index', compact('instances', 'selectedInstance', 'vms', 'error'));
    }

    public function start(Request $request, $vmid)
    {
        $instanceId = $request->input('instance_id');
        $node = $request->input('node');

        $plugin = Plugin::findOrFail($instanceId);
        /** @var \Plugins\Provisionings\Proxmox\ProxmoxProvisioning $pluginClass */
        $pluginClass = new \Plugins\Provisionings\Proxmox\ProxmoxProvisioning(app());
        $pluginClass->setPluginModel($plugin);
        $pluginClass->setInstanceConfig($plugin->config ?? []);

        try {
            $response = $pluginClass->getHttpFormClient()->post($pluginClass->getApiUrl("/nodes/{$node}/qemu/{$vmid}/status/start"));
            if ($response->successful()) {
                return back()->with('success', "Sent start command to VM {$vmid}.");
            }
            return back()->with('error', "Failed to start VM {$vmid}. HTTP " . $response->status());
        } catch (\Exception $e) {
            return back()->with('error', 'Error starting VM: ' . $e->getMessage());
        }
    }

    public function stop(Request $request, $vmid)
    {
        $instanceId = $request->input('instance_id');
        $node = $request->input('node');

        $plugin = Plugin::findOrFail($instanceId);
        /** @var \Plugins\Provisionings\Proxmox\ProxmoxProvisioning $pluginClass */
        $pluginClass = new \Plugins\Provisionings\Proxmox\ProxmoxProvisioning(app());
        $pluginClass->setPluginModel($plugin);
        $pluginClass->setInstanceConfig($plugin->config ?? []);

        try {
            $response = $pluginClass->getHttpFormClient()->post($pluginClass->getApiUrl("/nodes/{$node}/qemu/{$vmid}/status/stop"));
            if ($response->successful()) {
                return back()->with('success', "Sent stop command to VM {$vmid}.");
            }
            return back()->with('error', "Failed to stop VM {$vmid}. HTTP " . $response->status());
        } catch (\Exception $e) {
            return back()->with('error', 'Error stopping VM: ' . $e->getMessage());
        }
    }
}
