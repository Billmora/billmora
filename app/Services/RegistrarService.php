<?php

namespace App\Services;

use App\Models\Registrant;
use App\Models\Tld;
use App\Exceptions\RegistrarException;

class RegistrarService
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
     * Boot and return the registrar plugin instance and its configuration for the given registrant.
     *
     * @param  \App\Models\Registrant  $registrant
     * @return array{0: \App\Contracts\RegistrarInterface, 1: array<string, mixed>}
     *
     * @throws \App\Exceptions\RegistrarException
     */
    public function bootPluginFor(Registrant $registrant): array
    {
        if (!$registrant->plugin) {
            throw new RegistrarException(__('admin/registrars.provider.driver_missing'));
        }

        if (!$registrant->plugin->is_active) {
            throw new RegistrarException(__('admin/registrars.provider.disabled', ['name' => $registrant->plugin->name]));
        }

        $plugin = $this->manager->bootInstance($registrant->plugin);

        if (!$plugin) {
            throw new RegistrarException(__('admin/registrars.provider.driver_class_missing', [
                'driver' => $registrant->plugin->provider
            ]));
        }

        $instanceConfig = $registrant->plugin->config ?? [];

        return [$plugin, $instanceConfig];
    }

    /**
     * Boot and return the registrar plugin instance and its configuration for the given Tld.
     *
     * @param  \App\Models\Tld  $tld
     * @return array{0: \App\Contracts\RegistrarInterface, 1: array<string, mixed>}
     *
     * @throws \App\Exceptions\RegistrarException
     */
    public function bootPluginForTld(Tld $tld): array
    {
        if (!$tld->plugin) {
            throw new RegistrarException(__('admin/registrars.provider.driver_missing'));
        }

        if (!$tld->plugin->is_active) {
            throw new RegistrarException(__('admin/registrars.provider.disabled', ['name' => $tld->plugin->name]));
        }

        $plugin = $this->manager->bootInstance($tld->plugin);

        if (!$plugin) {
            throw new RegistrarException(__('admin/registrars.provider.driver_class_missing', [
                'driver' => $tld->plugin->provider
            ]));
        }

        $instanceConfig = $tld->plugin->config ?? [];

        return [$plugin, $instanceConfig];
    }
}
