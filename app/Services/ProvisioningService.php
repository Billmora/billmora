<?php

namespace App\Services;

use App\Models\Service;
use Exception;

class ProvisioningService
{
   /**
     * Inject the PluginManager dependency into the service.
     *
     * @param  \App\Services\PluginManager  $manager
     */
    public function __construct(
        private PluginManager $manager
    ) {}

    /**
     * Boot and return the provisioning plugin instance and its configuration for the given service.
     *
     * @param  \App\Models\Service  $service
     * @return array{0: \App\Contracts\ProvisioningInterface, 1: array<string, mixed>}
     *
     * @throws \Exception
     */
    public function bootPluginFor(Service $service): array
    {
        if (!$service->provisioning) {
            throw new Exception(__('admin/provisionings.provider.driver_missing'));
        }

        if (!$service->provisioning->is_active) {
            throw new Exception(__('validation.provisioning_disabled', ['name' => $service->provisioning->name]));
        }

        $plugin = $this->manager->bootInstance($service->provisioning);

        if (!$plugin) {
            throw new Exception(__('admin/provisionings.provider.driver_class_missing', [
                'driver' => $service->provisioning->provider
            ]));
        }

        $instanceConfig = $service->provisioning->config ?? [];

        return [$plugin, $instanceConfig];
    }
}