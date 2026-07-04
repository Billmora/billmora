<?php

namespace Plugins\Provisionings\Proxmox\Jobs;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Plugins\Provisionings\Proxmox\ProxmoxProvisioning;

class ProxmoxSetupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(
        public readonly int    $serviceId,
        public readonly int    $vmId,
        public readonly string $node,
        public readonly string $cloneUpid,
        public readonly array  $config,
        public readonly array  $configPayload,
        public readonly bool   $startOnCreate,
        public readonly array  $instanceConfig,
    ) {}

    public function handle(): void
    {
        $service = Service::find($this->serviceId);
        if (!$service) {
            return;
        }

        // AbstractPlugin requires the $app instance; setInstanceConfig restores
        // connection credentials that are not loaded from the Plugin model in jobs.
        $provisioning = new ProxmoxProvisioning(app());
        $provisioning->setInstanceConfig($this->instanceConfig);

        set_time_limit(0);

        $diskSize = (int) ($this->config['disk_size'] ?? 0);

        $provisioning->waitForTaskPublic($this->node, $this->cloneUpid);
        $provisioning->configureVmPublic($this->node, $this->vmId, $this->configPayload);

        if ($diskSize > 0) {
            $provisioning->resizeDiskPublic($this->node, $this->vmId, $diskSize);
        }

        if ($this->startOnCreate) {
            $provisioning->startVmPublic($this->node, $this->vmId);
        }
    }
}
