<?php

namespace App\Contracts;

use App\Models\Service;
use App\Models\Provisioning;
use Illuminate\Database\Eloquent\Model;

interface ProvisioningInterface
{
    /**
     * Get default configuration for provisioning provider.
     *
     * @return array
     */
    public static function getConfig(): array;

    /**
     * Get package configuration fields for provider integration.
     *
     * @param \App\Models\Provisioning|null $instance
     * @return array
     */
    public function getPackageFields(?Provisioning $instance = null): array;

    /**
     * Test connection to provisioning provider.
     *
     * @param array $config
     * @return bool
     */
    public function testConnection(array $config): bool;

    /**
     * Create service instance on provider.
     *
     * @param \App\Models\Service $service
     * @param array $instanceConfig
     * @param array $inputs
     * @return void
     */
    public function create(Service $service, array $instanceConfig, array $inputs): void;

    /**
     * Suspend service on provider.
     *
     * @param \App\Models\Service $service
     * @param array $instanceConfig
     * @return void
     */
    public function suspend(Service $service, array $instanceConfig): void;

    /**
     * Unsuspend service on provider.
     *
     * @param \App\Models\Service $service
     * @param array $instanceConfig
     * @return void
     */
    public function unsuspend(Service $service, array $instanceConfig): void;

    /**
     * Terminate service and remove from provider.
     *
     * @param \App\Models\Service $service
     * @param array $instanceConfig
     * @return void
     */
    public function terminate(Service $service, array $instanceConfig): void;

    /**
     * Renew service on provider.
     *
     * @param \App\Models\Service $service
     * @param array $instanceConfig
     * @return void
     */
    public function renew(Service $service, array $instanceConfig): void;

    /**
     * Change package for existing service.
     *
     * @param \App\Models\Service $service
     * @param array $instanceConfig
     * @param \Illuminate\Database\Eloquent\Model $newPackage
     * @return void
     */
    public function changePackage(Service $service, array $instanceConfig, Model $newPackage): void;

    /**
     * Change password for service on provider.
     *
     * @param \App\Models\Service $service
     * @param array $instanceConfig
     * @param string $newPassword
     * @return void
     */
    public function changePassword(Service $service, array $instanceConfig, string $newPassword): void;

    /**
     * Get available client actions for the service.
     *
     * @param \App\Models\Service $service
     * @return array<int, array<string, mixed>>
     */
    public function getClientActions(Service $service): array;

    /**
     * Get form configuration for a specific client action.
     *
     * @param \App\Models\Service $service
     * @param string $slug
     * @return array<string, mixed>|null
     */
    public function getClientActionForm(Service $service, string $slug): ?array;

    /**
     * Process and execute a client action with provided data.
     *
     * @param \App\Models\Service $service
     * @param string $slug
     * @param array<string, mixed> $data
     * @return mixed
     */
    public function processClientAction(Service $service, string $slug, array $data): mixed;

}