<?php

namespace Plugins\Provisionings\DirectAdmin;

use App\Support\AbstractPlugin;
use App\Contracts\ProvisioningInterface;
use App\Models\Service;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DirectAdminProvisioning extends AbstractPlugin implements ProvisioningInterface
{
    /*
    |--------------------------------------------------------------------------
    | Plugin Contract
    |--------------------------------------------------------------------------
    */

    public function getConfigSchema(): array
    {
        return [
            'host' => [
                'type'        => 'url',
                'label'       => 'Panel URL',
                'placeholder' => 'https://server.example.com:2222',
                'helper'      => 'DirectAdmin panel URL including port (default: 2222).',
                'rules'       => 'required|url',
            ],
            'username' => [
                'type'  => 'text',
                'label' => 'Reseller Username',
                'rules' => 'required|string',
            ],
            'password' => [
                'type'  => 'password',
                'label' => 'Reseller Password',
                'rules' => 'required|string',
            ],
        ];
    }

    public function getPackageSchema(): array
    {
        $packages = $this->_fetchAvailablePackages();

        return [
            'package_name' => [
                'type'    => 'select',
                'label'   => 'DirectAdmin Package',
                'options' => $packages,
                'helper'  => empty($packages)
                    ? 'No packages found. Ensure the plugin connection is configured and the DirectAdmin server is reachable.'
                    : 'Select a User Package from your DirectAdmin server.',
                'rules'   => 'required|string',
            ],
            'ip' => [
                'type'    => 'select',
                'label'   => 'IP Assignment',
                'options' => [
                    'server' => 'Server IP (Main)',
                    'shared' => 'Shared IP (Reseller Default)',
                    'assign' => 'Assign (Dedicated IP)',
                ],
                'default' => 'server',
                'rules'   => 'required|in:server,shared,assign',
            ],
            'notify' => [
                'type'    => 'toggle',
                'label'   => 'Send DA Welcome Email',
                'helper'  => 'Send DirectAdmin built-in welcome email to the user upon account creation.',
                'default' => false,
                'rules'   => 'boolean',
            ],
        ];
    }

    public function getCheckoutSchema(): array
    {
        return [
            'domain' => [
                'type'        => 'text',
                'label'       => 'Domain Name',
                'placeholder' => 'example.com',
                'rules'       => 'required|string|regex:/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Provisioning Contract
    |--------------------------------------------------------------------------
    */

    public function testConnection(array $config): bool
    {
        $response = Http::withBasicAuth($config['username'], $config['password'])
            ->asForm()
            ->get(rtrim($config['host'], '/') . '/CMD_API_SHOW_RESELLER_IPS');

        if (!$response->successful()) {
            throw new \Exception('Failed to connect to DirectAdmin. HTTP Status: ' . $response->status());
        }

        return true;
    }

    public function create(Service $service): void
    {
        $user = $service->user;
        $config = $service->package->provisioning_config ?? [];
        $clientInput = $service->configuration ?? [];

        if (empty($config['package_name'])) {
            throw new \Exception('DirectAdmin Package Configuration is missing. Please configure the package in Admin Panel first.');
        }

        $domain = $clientInput['domain'] ?? null;

        if (empty($domain)) {
            throw new \Exception('Domain name is required. Please provide a valid domain during checkout.');
        }

        // 1. Generate credentials
        $username = $this->_generateUsername($domain);
        $password = $this->_generatePassword();

        // 2. Create account on DirectAdmin
        $result = $this->_request('POST', 'CMD_API_ACCOUNT_USER', [
            'action'   => 'create',
            'add'      => 'Submit',
            'username' => $username,
            'email'    => $user->email,
            'passwd'   => $password,
            'passwd2'  => $password,
            'domain'   => $domain,
            'package'  => $config['package_name'],
            'ip'       => $config['ip'] ?? 'server',
            'notify'   => filter_var($config['notify'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'yes' : 'no',
        ]);

        // 3. Store the DA username in service configuration for future operations
        $service->update([
            'configuration' => array_merge($clientInput, [
                'da_username' => $username,
            ]),
        ]);
    }

    public function suspend(Service $service): void
    {
        $username = $this->_resolveUsername($service);

        $this->_request('POST', 'CMD_API_SELECT_USERS', [
            'location' => 'CMD_SELECT_USERS',
            'suspend'  => 'Suspend',
            'select0'  => $username,
        ]);
    }

    public function unsuspend(Service $service): void
    {
        $username = $this->_resolveUsername($service);

        $this->_request('POST', 'CMD_API_SELECT_USERS', [
            'location' => 'CMD_SELECT_USERS',
            'suspend'  => 'Unsuspend',
            'select0'  => $username,
        ]);
    }

    public function terminate(Service $service): void
    {
        $username = $this->_resolveUsername($service);

        $this->_request('POST', 'CMD_API_SELECT_USERS', [
            'confirmed' => 'Confirm',
            'delete'    => 'yes',
            'select0'   => $username,
        ]);
    }

    public function renew(Service $service): void
    {
        // No action required on DirectAdmin side for renewal (billing only)
    }

    public function scale(Service $service, array $newConfig): void
    {
        $username = $this->_resolveUsername($service);
        $config = $service->package->provisioning_config ?? [];

        if (empty($config['package_name'])) {
            throw new \Exception('Scale Failed: DirectAdmin Package Configuration is missing or invalid.');
        }

        $this->_request('POST', 'CMD_API_MODIFY_USER', [
            'action'  => 'package',
            'user'    => $username,
            'package' => $config['package_name'],
        ]);
    }

    public function getClientAction(Service $service): array
    {
        return [
            'login' => [
                'label' => 'Login to Control Panel',
                'icon'  => 'fa-solid fa-server',
                'type'  => 'link',
            ],
        ];
    }

    public function handleClientAction(Service $service, string $slug, array $data = [])
    {
        if ($slug === 'login') {
            $username = $this->_resolveUsername($service);
            $randomKey = Str::password(32, true, true, false, false);

            $this->_request('POST', 'CMD_API_LOGIN_KEYS', [
                'action'       => 'create',
                'user'         => $username, 
                'type'         => 'key',
                'keyname'      => 'billmorasso' . Str::random(8),
                'key'          => $randomKey,
                'key2'         => $randomKey,
                'expires'      => 'yes',
                'expire_unit'  => 'minute',
                'expire_value' => '5',
                'max_uses'     => '1',
                'clear_key'    => 'yes',
                'allow_htm'    => 'yes',
                'passwd'       => $this->getInstanceConfig('password'), 
            ]);

            $host = rtrim($this->getInstanceConfig('host'), '/');
            
            $loginUrl = $host . '/CMD_LOGIN?' . http_build_query([
                'username' => $username,
                'password' => $randomKey,
            ]);

            return redirect()->away($loginUrl);
        }

        throw new \Exception("Unknown action requested: {$slug}");
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    /**
     * Send an API request to the DirectAdmin server.
     *
     * DirectAdmin Legacy API uses form-encoded POST/GET with Basic Auth.
     * Response is URL-encoded key=value pairs.
     *
     * @param string $method HTTP method (GET or POST)
     * @param string $command DA API command (e.g. CMD_API_ACCOUNT_USER)
     * @param array<string, mixed> $params Request parameters
     * @return array<string, mixed> Parsed response
     * @throws \Exception
     */
    private function _request(string $method, string $command, array $params = []): array
    {
        // Increase PHP execution time limit to prevent web requests from timing out
        // as DirectAdmin server creation / restoration operations can be time-consuming
        set_time_limit(120);

        $url = rtrim($this->getInstanceConfig('host'), '/') . '/' . $command;

        $http = Http::withBasicAuth(
            $this->getInstanceConfig('username'),
            $this->getInstanceConfig('password')
        )->timeout(120)->asForm();

        $response = strtoupper($method) === 'GET'
            ? $http->get($url, $params)
            : $http->post($url, $params);

        if (!$response->successful()) {
            throw new \Exception("DirectAdmin API request failed. HTTP Status: {$response->status()}");
        }

        // Parse DA's URL-encoded response
        $parsed = [];
        parse_str($response->body(), $parsed);

        // DA returns error=1 on failure
        if (isset($parsed['error']) && (int) $parsed['error'] === 1) {
            $message = $parsed['text'] ?? 'Unknown DirectAdmin error';
            $details = $parsed['details'] ?? '';

            throw new \Exception("DirectAdmin Error: {$message}" . ($details ? " — {$details}" : ''));
        }

        return $parsed;
    }

    /**
     * Resolve the DirectAdmin username from the service configuration.
     *
     * @param Service $service
     * @return string
     * @throws \Exception
     */
    private function _resolveUsername(Service $service): string
    {
        $username = $service->configuration['da_username'] ?? null;

        if (empty($username)) {
            throw new \Exception('Action Aborted: No DirectAdmin username found for this Service (#' . $service->id . '). The account may not have been created.');
        }

        return $username;
    }

    /**
     * Generate a DirectAdmin-compatible username from a domain name.
     *
     * DA username requirements: 4-8 alphanumeric characters, must start with a letter.
     * Strategy: take first 5 letters from domain + 3 random digits.
     *
     * @param string $domain
     * @return string
     */
    private function _generateUsername(string $domain): string
    {
        // Strip TLD and non-alpha characters, take first 5 letters
        $base = preg_replace('/[^a-z]/', '', strtolower(explode('.', $domain)[0]));
        $base = substr($base, 0, 5);

        // Ensure minimum 2 chars for base
        if (strlen($base) < 2) {
            $base = 'user';
        }

        // Append random digits to fill up to 8 chars max
        $remainingLength = 8 - strlen($base);
        $suffix = '';
        for ($i = 0; $i < $remainingLength; $i++) {
            $suffix .= random_int(0, 9);
        }

        return $base . $suffix;
    }

    /**
     * Generate a secure random password for the DirectAdmin account.
     *
     * @return string 12 characters: uppercase + lowercase + digits
     */
    private function _generatePassword(): string
    {
        // DirectAdmin passes passwords through API/bash, which can conflict with symbols.
        // We generate a 12-character alphanumeric-only password.
        return Str::password(12, true, true, false, false);
    }

    /**
     * Fetch available User Packages from the DirectAdmin server.
     *
     * Calls CMD_API_PACKAGES_USER which returns a URL-encoded list[]=PackageName format.
     * Gracefully returns an empty array if the connection fails or config is missing.
     *
     * @return array<string, string> ['PackageName' => 'PackageName', ...]
     */
    private function _fetchAvailablePackages(): array
    {
        try {
            $host = $this->getInstanceConfig('host');
            $username = $this->getInstanceConfig('username');
            $password = $this->getInstanceConfig('password');

            if (empty($host) || empty($username) || empty($password)) {
                return [];
            }

            $response = Http::withBasicAuth($username, $password)
                ->asForm()
                ->timeout(5)
                ->get(rtrim($host, '/') . '/CMD_API_PACKAGES_USER');

            if (!$response->successful()) {
                return [];
            }

            $parsed = [];
            parse_str($response->body(), $parsed);

            $packageNames = $parsed['list'] ?? [];

            if (!is_array($packageNames)) {
                return [];
            }

            $options = [];
            foreach ($packageNames as $name) {
                $options[$name] = $name;
            }

            return $options;
        } catch (\Exception $e) {
            return [];
        }
    }
}
