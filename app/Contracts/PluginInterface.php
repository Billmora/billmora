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
     * Bootstrap plugin services including event listeners and schedules.
     *
     * @return void
     */
    public function boot(): void;
}