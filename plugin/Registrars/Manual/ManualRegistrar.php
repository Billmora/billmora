<?php

namespace Plugins\Registrars\Manual;

use App\Contracts\RegistrarInterface;
use App\Models\Registrant;
use App\Support\AbstractPlugin;
use Illuminate\Support\Facades\Cache;

class ManualRegistrar extends AbstractPlugin implements RegistrarInterface
{
    /**
     * Get global configuration schema for admin panel.
     */
    public function getConfigSchema(): array
    {
        return [
            'custom_whois_servers' => [
                'type' => 'textarea',
                'label' => 'Custom WHOIS Servers',
                'required' => false,
                'default' => '',
                'helper' => "Format: tld=whois.server.com (one per line). Example: id=whois.pandi.or.id, these servers will override the default IANA lookup.",
            ],
            'timeout' => [
                'type' => 'number',
                'label' => 'WHOIS Timeout (Seconds)',
                'required' => false,
                'default' => '5',
                'helper' => 'Maximum time to wait for a WHOIS server response.',
            ],
            'use_dns_fallback' => [
                'type' => 'toggle',
                'label' => 'DNS Fallback',
                'default' => true,
                'helper' => 'Use DNS lookup fallback if WHOIS lookup fails.',
            ]
        ];
    }

    /**
     * Test connection to registrar provider.
     */
    public function testConnection(array $config): bool
    {
        return true;
    }

    /**
     * Check domain availability via WHOIS server.
     */
    public function checkAvailability(string $domain): array
    {
        $domain = strtolower(trim($domain));
        $tld = substr(strrchr($domain, "."), 1);
        
        $available = false;
        
        $whoisServer = $this->getWhoisServerForTld($tld);
        
        if ($whoisServer) {
            $whoisResult = $this->queryWhois($whoisServer, $domain);
            
            if ($whoisResult) {
                $available = $this->parseWhoisAvailability($whoisResult);
            } else if ($this->getInstanceConfig('use_dns_fallback', true)) {
                $available = $this->checkDns($domain);
            }
        } else if ($this->getInstanceConfig('use_dns_fallback', true)) {
            $available = $this->checkDns($domain);
        }

        return [
            'available' => $available,
            'premium' => false,
            'price' => null,
        ];
    }

    /**
     * Parse WHOIS string to determine availability based on common phrases.
     */
    private function parseWhoisAvailability(string $response): bool
    {
        $not_found_strings = [
            'no match', 'not found', 'is free', 'available', 'not registered',
            'no data found', 'no entries found', 'status: free'
        ];
        
        $responseLower = strtolower($response);
        
        foreach ($not_found_strings as $str) {
            if (str_contains($responseLower, $str)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Fallback: check if domain has any DNS records.
     * Note: Registered domains without DNS will appear available here,
     * so it's not perfectly accurate, but better than nothing if WHOIS is down.
     */
    private function checkDns(string $domain): bool
    {
        $records = @dns_get_record($domain, DNS_ANY);
        return empty($records);
    }

    /**
     * Get the WHOIS server for a TLD, cached for 30 days.
     */
    private function getWhoisServerForTld(string $tld): ?string
    {
        $tld = ltrim(strtolower($tld), '.');
        
        // 1. Check custom WHOIS servers defined by Admin
        $customServersRaw = $this->getInstanceConfig('custom_whois_servers', '');
        if (!empty($customServersRaw)) {
            foreach (explode("\n", $customServersRaw) as $line) {
                if (str_contains($line, '=')) {
                    [$customTld, $customServer] = explode('=', trim($line), 2);
                    if (ltrim(strtolower(trim($customTld)), '.') === $tld) {
                        return trim($customServer);
                    }
                }
            }
        }
        
        $cacheKey = 'registrar_whois_server_' . $tld;
        
        return Cache::remember($cacheKey, 86400 * 30, function() use ($tld) {
            // Check well-known mapped servers from local JSON dictionary first
            $knownServersFile = __DIR__ . '/whois.json';
            $knownServers = file_exists($knownServersFile) 
                ? json_decode(file_get_contents($knownServersFile), true) 
                : [];
            
            if (isset($knownServers[$tld])) {
                return $knownServers[$tld];
            }
            
            // Ask IANA for the WHOIS server
            $result = $this->queryWhois('whois.iana.org', $tld);
            if ($result && preg_match('/whois:\s+([a-z0-9\.\-]+)/i', $result, $matches)) {
                return $matches[1];
            }
            
            return null;
        });
    }

    /**
     * Perform socket connection to query WHOIS.
     */
    private function queryWhois(string $server, string $query): ?string
    {
        $timeout = (int) $this->getInstanceConfig('timeout', 5);
        
        try {
            $fp = @fsockopen($server, 43, $errno, $errstr, $timeout);
            if (!$fp) return null;
            
            stream_set_timeout($fp, $timeout);
            
            // Format specific queries for certain servers
            if ($server === 'whois.verisign-grs.com') {
                $query = '=' . $query;
            }
            
            fputs($fp, $query . "\r\n");
            
            $out = "";
            while (!feof($fp)) {
                $out .= fgets($fp);
            }
            fclose($fp);
            
            return $out;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Create registrant (Offline).
     */
    public function create(Registrant $registrant): void
    {
        app('log')->info("Manual Registrar: Manual registration triggered for {$registrant->domain}");
    }

    /**
     * Transfer registrant (Offline).
     */
    public function transfer(Registrant $registrant, string $eppCode): void
    {
        app('log')->info("Manual Registrar: Manual transfer triggered for {$registrant->domain} with EPP {$eppCode}");
    }

    /**
     * Renew registrant (Offline).
     */
    public function renew(Registrant $registrant, int $years = 1): void
    {
        app('log')->info("Manual Registrar: Manual renewal triggered for {$registrant->domain} for {$years} years");
    }

    /**
     * Get nameservers.
     */
    public function getNameservers(Registrant $registrant): array
    {
        $configuration = $registrant->configuration ?? [];
        return $configuration['nameservers'] ?? ['ns1.example.com', 'ns2.example.com'];
    }

    /**
     * Set nameservers.
     */
    public function setNameservers(Registrant $registrant, array $nameservers): void
    {
        app('log')->info("Manual Registrar: Saved nameservers locally for {$registrant->domain}", $nameservers);
        
        $configuration = $registrant->configuration ?? [];
        $configuration['nameservers'] = $nameservers;
        
        $registrant->update([
            'configuration' => $configuration
        ]);
    }

    /**
     * Get EPP Code.
     */
    public function getEPPCode(Registrant $registrant): string
    {
        return "MANUAL-" . strtoupper(substr(md5($registrant->domain), 0, 8));
    }

    /**
     * Get Whois Info.
     */
    public function getWhoisInfo(Registrant $registrant): array
    {
        return [
            'registrar' => 'Manual Registration',
            'creation_date' => $registrant->registered_at ? $registrant->registered_at->toDateString() : now()->toDateString(),
            'expiration_date' => $registrant->expires_at ? $registrant->expires_at->toDateString() : now()->addYear()->toDateString(),
        ];
    }

    /**
     * Set Whois Privacy.
     */
    public function setWhoisPrivacy(Registrant $registrant, bool $enabled): void
    {
        app('log')->info("Manual Registrar: Saved WHOIS Privacy status locally for {$registrant->domain}");
        $registrant->update([
            'whois_privacy' => $enabled
        ]);
    }

    /**
     * Sync Status.
     */
    public function syncStatus(Registrant $registrant): array
    {
        $status = $registrant->status;
        
        if ($registrant->expires_at && $registrant->expires_at->isPast()) {
            $status = 'expired';
        }
        
        return [
            'status' => $status,
            'expires_at' => $registrant->expires_at ? $registrant->expires_at->toDateTimeString() : null,
        ];
    }
}
