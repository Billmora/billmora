<?php

namespace App\Listeners\Service;

use App\Events\Service as ServiceEvents;
use App\Events\Service\ProvisioningTerminated;
use App\Services\ProvisioningService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeprovisionOnServiceTerminated
{
    use InteractsWithQueue;

    public int $timeout = 60;
    public int $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct(
        private ProvisioningService $provisioningService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ProvisioningTerminated $event): void
    {
        $service = $event->service;
        $service->loadMissing('provisioning');

        if (!$service->provisioning) {
            return;
        }

        try {
            [$plugin, $instanceConfig] = $this->provisioningService->bootPluginFor($service);

            $plugin->terminate($service, $instanceConfig);

        } catch (\Throwable $e) {
            event(new ServiceEvents\ProvisioningFailed($service, $e->getMessage(), 'terminate'));
            
            $this->fail($e);
        }
    }
}
