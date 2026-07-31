<?php

namespace Plugins\Provisionings\Proxmox\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plugin;

class NodeController extends Controller
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

        $nodes = [];
        $error = null;

        try {
            $response = $pluginClass->getHttpClient()->get($pluginClass->getApiUrl('/nodes'));
            if ($response->successful()) {
                $nodeList = $response->json('data', []);
                
                // Fetch storage & QEMU count for each node
                foreach ($nodeList as &$node) {
                    $nodeName = $node['node'];
                    
                    // Default values
                    $node['storage_usage'] = 0;
                    $node['storage_total'] = 0;
                    $node['qemu_running'] = 0;
                    $node['qemu_total'] = 0;

                    if ($node['status'] === 'online') {
                        // Storage
                        $storageResp = $pluginClass->getHttpClient()->get($pluginClass->getApiUrl("/nodes/{$nodeName}/storage"));
                        if ($storageResp->successful()) {
                            $storages = $storageResp->json('data', []);
                            foreach ($storages as $storage) {
                                // Only sum active storages
                                if (($storage['active'] ?? 0) == 1) {
                                    $node['storage_usage'] += $storage['used'] ?? 0;
                                    $node['storage_total'] += $storage['total'] ?? 0;
                                }
                            }
                        }

                        // QEMU
                        $qemuResp = $pluginClass->getHttpClient()->get($pluginClass->getApiUrl("/nodes/{$nodeName}/qemu"));
                        if ($qemuResp->successful()) {
                            $vms = $qemuResp->json('data', []);
                            $node['qemu_total'] = count($vms);
                            $node['qemu_running'] = collect($vms)->where('status', 'running')->count();
                        }
                    }
                }
                $nodes = $nodeList;
            } else {
                $error = 'Failed to connect to Proxmox API: HTTP ' . $response->status();
            }
        } catch (\Exception $e) {
            $error = 'Connection error: ' . $e->getMessage();
        }

        return view('provisioning.proxmox::admin.nodes.index', compact('instances', 'selectedInstance', 'nodes', 'error'));
    }
}
