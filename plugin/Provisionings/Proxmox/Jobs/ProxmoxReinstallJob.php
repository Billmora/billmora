<?php

namespace Plugins\Provisionings\Proxmox\Jobs;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Plugins\Provisionings\Proxmox\ProxmoxProvisioning;

class ProxmoxReinstallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(
        public readonly int     $serviceId,
        public readonly int     $templateId,
        public readonly string  $hostname,
        public readonly ?string $password,
        public readonly bool    $startOnCreate,
        public readonly array   $instanceConfig,
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

        $provisioning->reinstallVmPublic($service, $this->templateId, $this->hostname, $this->password, $this->startOnCreate);
    }
}
