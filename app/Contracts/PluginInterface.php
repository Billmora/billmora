<?php

namespace App\Contracts;

interface PluginInterface
{
    /**
     * Get the plugin provider name identifier.
     *
     * @return string
     */
    public function getProvider(): string;

    /**
     * Get global configuration schema for admin panel settings.
     *
     * @return array<string, mixed>
     */
    public function getConfigSchema(): array;

    /**
     * Get the navigation items for the admin area.
     *
     * @return array<string, array<int, mixed>>
     */
    public function getNavigationAdmin(): array;

    /**
     * Get the navigation items for the client area.
     *
     * @return array<string, array<int, mixed>>
     */
    public function getNavigationClient(): array;

    /**
     * Get the navigation items for the portal area.
     *
     * @return array<string, array<int, mixed>>
     */
    public function getNavigationPortal(): array;

    /**
     * Bootstrap plugin services including event listeners and schedules.
     *
     * @return void
     */
    public function boot(): void;
}