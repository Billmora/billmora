<?php

namespace App\Contracts;

use App\Models\Registrant;

interface RegistrarInterface extends PluginInterface
{
    /**
     * Get global configuration schema for admin panel (API key, reseller ID, dll).
     */
    public function getConfigSchema(): array;

    /**
     * Test connection to registrar provider.
     */
    public function testConnection(array $config): bool;

    /**
     * Check domain availability.
     *
     * @return array{available: bool, premium: bool, price: ?float}
     */
    public function checkAvailability(string $domain): array;

    /**
     * Create a new domain registration.
     */
    public function create(Registrant $registrant): void;

    /**
     * Initiate domain transfer.
     */
    public function transfer(Registrant $registrant, string $eppCode): void;

    /**
     * Renew domain registration.
     */
    public function renew(Registrant $registrant, int $years = 1): void;

    /**
     * Get current nameservers for domain.
     *
     * @return array<int, string>
     */
    public function getNameservers(Registrant $registrant): array;

    /**
     * Set nameservers for domain.
     *
     * @param array<int, string> $nameservers
     */
    public function setNameservers(Registrant $registrant, array $nameservers): void;

    /**
     * Get EPP/authorization code for domain transfer out.
     */
    public function getEPPCode(Registrant $registrant): string;

    /**
     * Get WHOIS information for domain.
     *
     * @return array<string, mixed>
     */
    public function getWhoisInfo(Registrant $registrant): array;

    /**
     * Enable or disable WHOIS privacy protection.
     */
    public function setWhoisPrivacy(Registrant $registrant, bool $enabled): void;

    /**
     * Sync domain status from registrar (for cron/scheduled task).
     *
     * @return array{status: string, expires_at: ?string}
     */
    public function syncStatus(Registrant $registrant): array;
}
