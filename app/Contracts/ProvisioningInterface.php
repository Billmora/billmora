<?php

namespace App\Contracts;

use App\Models\Service;

interface ProvisioningInterface extends PluginInterface
{
    /**
     * Get package configuration schema for provisioning settings.
     *
     * @return array<string, mixed>
     */
    public function getPackageSchema(): array;

    /**
     * Get checkout form schema for service configuration fields.
     *
     * @return array<string, mixed>
     */
    public function getCheckoutSchema(): array;

    /**
     * Test connection to provisioning provider with given configuration.
     *
     * @param array<string, mixed> $config Data from Plugin::$config
     * @return bool
     */
    public function testConnection(array $config): bool;

    /**
     * Create service instance on provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return void
     */
    public function create(Service $service): void;

    /**
     * Suspend service on provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return void
     */
    public function suspend(Service $service): void;

    /**
     * Unsuspend service on provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return void
     */
    public function unsuspend(Service $service): void;

    /**
     * Terminate service and remove from provisioning provider.
     *
     * @param \App\Models\Service $service
     * @return void
     */
    public function terminate(Service $service): void;

    /**
     * Renew service on provisioning provider by extending expiration.
     *
     * @param \App\Models\Service $service
     * @return void
     */
    public function renew(Service $service): void;

    /**
     * Scale service to new configuration on provisioning provider.
     *
     * @param \App\Models\Service $service Service with updated configuration
     * @param array<string, mixed> $newConfig New configuration data
     * @return void
     */
    public function scale(Service $service, array $newConfig): void;

    /**
     * Get available client actions for the service.
     *
     * @param \App\Models\Service $service
     * @return array<int, array<string, mixed>>
     */
    public function getClientAction(Service $service): array;

    /**
     * Handle and execute a specific client action with provided data.
     *
     * @param \App\Models\Service $service
     * @param string $slug
     * @param array<string, mixed> $data
     * @return mixed
     */
    public function handleClientAction(Service $service, string $slug, array $data = []);
}
