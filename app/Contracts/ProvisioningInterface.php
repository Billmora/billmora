<?php

namespace App\Contracts;

use App\Models\Package;
use App\Models\Service;

interface ProvisioningInterface
{
    /**
     * Get default form fields for provisioning provider configuration.
     *
     * @return array<string, mixed>
     */
    public static function getConfig(): array;

    /**
     * Get package configuration fields for provider integration.
     *
     * @param array|null<string, mixed> $instanceConfig Data from Provisioning::$config
     * @return array<string, mixed>
     */
    public function getPackageFields(?array $instanceConfig = []): array;

    /**
     * Test connection to provisioning provider.
     *
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @return bool Returns true if successful.
     * @throws \Exception If the connection fails or configuration is invalid.
     */
    public function testConnection(array $instanceConfig): bool;

    /**
     * Create service instance on provider.
     *
     * @param \App\Models\Service $service
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @return void
     */
    public function create(Service $service, array $instanceConfig): void;

    /**
     * Suspend service on provider.
     *
     * @param \App\Models\Service $service
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @return void
     */
    public function suspend(Service $service, array $instanceConfig): void;

    /**
     * Unsuspend service on provider.
     *
     * @param \App\Models\Service $service
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @return void
     */
    public function unsuspend(Service $service, array $instanceConfig): void;

    /**
     * Terminate service and remove from provider.
     *
     * @param \App\Models\Service $service
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @return void
     */
    public function terminate(Service $service, array $instanceConfig): void;

    /**
     * Renew service on provider by extending expiration.
     *
     * @param \App\Models\Service $service
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @return void
     */
    public function renew(Service $service, array $instanceConfig): void;

    /**
     * Scale the service to a new package with updated configuration.
     *
     * @param \App\Models\Service $service Service with new package_id and configuration
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @param \App\Models\Package $oldPackage Previous package before scaling
     * @return void
     */
    public function scale(Service $service, array $instanceConfig, Package $oldPackage): void;

    /**
     * Get available client actions for the service.
     *
     * @param \App\Models\Service $service
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @return array<int, array<string, mixed>>
     */
    public function getClientActions(Service $service, array $instanceConfig): array;

    /**
     * Get form configuration for a specific client action.
     *
     * @param \App\Models\Service $service
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @param string $slug
     * @return array<string, mixed>|null
     */
    public function getClientActionForm(Service $service, array $instanceConfig, string $slug): ?array;

    /**
     * Process and execute a client action with provided data.
     *
     * @param \App\Models\Service $service
     * @param array<string, mixed> $instanceConfig Data from Provisioning::$config
     * @param string $slug
     * @param array<string, mixed> $actionData Input data from the client action form
     * @return mixed
     */
    public function processClientAction(Service $service, array $instanceConfig, string $slug, array $actionData): mixed;
}
