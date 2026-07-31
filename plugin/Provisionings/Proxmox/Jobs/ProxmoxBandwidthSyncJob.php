<?php

namespace Plugins\Provisionings\Proxmox\Jobs;

use App\Models\Plugin;
use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProxmoxBandwidthSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $plugins = Plugin::where('type', 'provisioning')
            ->where('provider', 'Proxmox')
            ->where('is_active', true)
            ->get();

        foreach ($plugins as $plugin) {
            /** @var \Plugins\Provisionings\Proxmox\ProxmoxProvisioning $pluginClass */
            $pluginClass = new \Plugins\Provisionings\Proxmox\ProxmoxProvisioning(app());
            $pluginClass->setPluginModel($plugin);
            $pluginClass->setInstanceConfig($plugin->config ?? []);

            $services = Service::where('plugin_id', $plugin->id)
                ->where('status', 'active')
                ->get();

            foreach ($services as $service) {
                $config = $service->configuration ?? [];
                if (!isset($config['proxmox_vmid']) || !isset($config['proxmox_node'])) {
                    continue;
                }

                $vmid = $config['proxmox_vmid'];
                $node = $config['proxmox_node'];

                try {
                    $response = $pluginClass->getHttpClient()->get($pluginClass->getApiUrl("/nodes/{$node}/qemu/{$vmid}/rrddata"), [
                        'timeframe' => 'month'
                    ]);

                    if ($response->successful()) {
                        $data = $response->json('data', []);
                        
                        $netin = 0;
                        $netout = 0;
                        $prevTime = 0;
                        
                        foreach ($data as $point) {
                            if (isset($point['netin']) && isset($point['netout']) && $prevTime > 0) {
                                $step = $point['time'] - $prevTime;
                                // netin/netout is in bytes per second, multiply by seconds in the step to get total bytes
                                $netin += $point['netin'] * $step;
                                $netout += $point['netout'] * $step;
                            }
                            $prevTime = $point['time'];
                        }

                        // Store in bytes
                        $config['bandwidth_usage_in'] = $netin;
                        $config['bandwidth_usage_out'] = $netout;

                        // Also attempt to fetch Guest Agent IP Addresses
                        try {
                            $agentResponse = $pluginClass->getHttpClient()->get($pluginClass->getApiUrl("/nodes/{$node}/qemu/{$vmid}/agent/network-get-interfaces"));
                            if ($agentResponse->successful()) {
                                $interfaces = $agentResponse->json('data.result', []);
                                $ipv4s = [];
                                $ipv6s = [];
                                foreach ($interfaces as $iface) {
                                    $name = $iface['name'] ?? '';
                                    if ($name === 'lo') continue;
                                    foreach ($iface['ip-addresses'] ?? [] as $addr) {
                                        $ip   = $addr['ip-address'] ?? '';
                                        $type = $addr['ip-address-type'] ?? '';
                                        if ($ip && $type === 'ipv4') $ipv4s[] = $ip;
                                        if ($ip && $type === 'ipv6') $ipv6s[] = $ip;
                                    }
                                }
                                if (!empty($ipv4s)) {
                                    $config['guest_ipv4'] = implode(', ', $ipv4s);
                                }
                                if (!empty($ipv6s)) {
                                    $config['guest_ipv6'] = implode(', ', array_map(function($i) { return explode('/', $i)[0]; }, $ipv6s));
                                }
                            }
                        } catch (\Exception $e) {
                            // Guest agent might not be running or installed, safely ignore.
                        }
                        
                        $service->update(['configuration' => $config]);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to sync bandwidth for service {$service->id}: " . $e->getMessage());
                }
            }
        }
    }
}
