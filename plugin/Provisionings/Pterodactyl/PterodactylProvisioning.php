<?php

namespace Plugins\Provisionings\Pterodactyl;

use App\Support\AbstractPlugin;
use App\Contracts\ProvisioningInterface;
use App\Models\Service;
use App\Exceptions\ProvisioningException;
use Illuminate\Support\Facades\Http;

class PterodactylProvisioning extends AbstractPlugin implements ProvisioningInterface
{
    private function _url(string $endpoint): string
    {
        return rtrim($this->getInstanceConfig('panel_url'), '/') . '/api/application' . $endpoint;
    }

    private function _headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getInstanceConfig('api_key'),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    public function getConfigSchema(): array
    {
        return [
            'panel_url' => [
                'type'        => 'url',
                'label'       => 'Pterodactyl Panel URL',
                'placeholder' => 'https://panel.example.com',
                'rules'       => 'required|url'
            ],
            'api_key' => [
                'type'        => 'password',
                'label'       => 'Application API Key',
                'helper'      => 'Requires an Application API key with read/write perms for Users, Servers, Nodes, and Locations.',
                'rules'       => 'required|string'
            ],
        ];
    }

    public function getPackageSchema(): array
    {
        return [
            'location_ids' => [
                'type'    => 'text',
                'label'   => 'Dedicated Location IDs',
                'helper'  => 'Comma separated (e.g. 1, 2). Used for auto-deployment allocation.',
                'rules'   => 'required|string',
            ],
            'dedicated_ip' => [
                'type'    => 'toggle',
                'label'   => 'Require Dedicated IP',
                'default' => false,
                'rules'   => 'boolean'
            ],
            'port_range' => [
                'type'    => 'text',
                'label'   => 'Port Range',
                'helper'  => 'Comma separated ports or ranges (e.g. 25565, 25570-25580).',
                'rules'   => 'nullable|string'
            ],
            'nest_id' => [
                'type'    => 'number',
                'label'   => 'Nest ID',
                'rules'   => 'required|integer',
            ],
            'egg_id' => [
                'type'    => 'number',
                'label'   => 'Egg ID',
                'rules'   => 'required|integer',
            ],
            'docker_image' => [
                'type'    => 'text',
                'label'   => 'Docker Image',
                'rules'   => 'nullable|string',
                'placeholder' => 'ghcr.io/pterodactyl/yolks:java_17',
                'helper'  => 'Leave empty to use the Egg\'s default Docker Image.'
            ],
            'startup' => [
                'type'    => 'text',
                'label'   => 'Startup Command',
                'rules'   => 'nullable|string',
                'placeholder' => 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar {{SERVER_JARFILE}}',
                'helper'  => 'Leave empty to use the Egg\'s default Startup Command.'
            ],
            'memory' => [
                'type'    => 'number',
                'label'   => 'Memory (MB)',
                'rules'   => 'required|integer|min:0',
                'default' => 1024
            ],
            'swap' => [
                'type'    => 'number',
                'label'   => 'Swap (MB)',
                'rules'   => 'required|integer|min:-1',
                'default' => 0
            ],
            'disk' => [
                'type'    => 'number',
                'label'   => 'Disk Space (MB)',
                'rules'   => 'required|integer|min:0',
                'default' => 5000
            ],
            'cpu' => [
                'type'    => 'number',
                'label'   => 'CPU Limit (%)',
                'helper'  => '100% is 1 core. 0 for unlimited.',
                'rules'   => 'required|integer|min:0',
                'default' => 100
            ],
            'io' => [
                'type'    => 'number',
                'label'   => 'Block IO Weight',
                'rules'   => 'required|integer|min:10|max:1000',
                'default' => 500
            ],
            'databases' => [
                'type'    => 'number',
                'label'   => 'Database Limit',
                'rules'   => 'required|integer|min:0',
                'default' => 0
            ],
            'allocations' => [
                'type'    => 'number',
                'label'   => 'Allocation Limit',
                'rules'   => 'required|integer|min:0',
                'default' => 0
            ],
            'backups' => [
                'type'    => 'number',
                'label'   => 'Backup Limit',
                'rules'   => 'required|integer|min:0',
                'default' => 0
            ],
            'oom_killer' => [
                'type'    => 'toggle',
                'label'   => 'Disable OOM Killer',
                'default' => false,
                'rules'   => 'boolean'
            ],
            'environment' => [
                'type'    => 'textarea',
                'label'   => 'Environment Variables (JSON)',
                'helper'  => 'Variables required by the Egg (e.g. {"SERVER_JARFILE": "server.jar"}). Leave empty to automatically use the Egg\'s default environment.',
                'rules'   => 'nullable|json',
            ],
        ];
    }

    public function getCheckoutSchema(): array
    {
        return [
            'server_name' => [
                'type'        => 'text',
                'label'       => 'Server Name',
                'placeholder' => 'My Minecraft Server',
                'rules'       => 'required|string|min:3|max:100'
            ],
        ];
    }


    public function testConnection(array $config): bool
    {
        $url = rtrim($config['panel_url'], '/') . '/api/application/users';
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $config['api_key'],
            'Accept'        => 'application/json',
        ])->get($url);

        if (!$response->successful()) {
            throw new ProvisioningException('Failed to connect to Pterodactyl. HTTP Status: ' . $response->status(), ['response' => $response->json() ?: $response->body()]);
        }

        return true;
    }

    public function create(Service $service): void
    {
        $user = $service->user;
        $config = $service->package->provisioning_config ?? [];
        $config = $this->_mergeVariants($service, $config);
        $clientInput = $service->configuration ?? [];

        if (!is_array($config) || empty($config['egg_id'])) {
            throw new \Exception('Pterodactyl Package Configuration is missing. Please configure the package in Admin Panel first.');
        }

        // 1. Sync User
        $pterodactylUserId = $this->_getOrCreateUser($user);

        $eggId = (int) ($config['egg_id'] ?? 0);
        $nestId = (int) ($config['nest_id'] ?? 0);
        
        // 2. Fetch Egg Data if fields are missing
        $dockerImage = $config['docker_image'] ?? '';
        $startup = $config['startup'] ?? '';
        $environment = ['P_SERVER_ALLOCATION_LIMIT' => (int)($config['allocations'] ?? 0)];
        $parsedEnv = !empty($config['environment']) ? json_decode($config['environment'], true) : [];

        if (empty($dockerImage) || empty($startup) || empty($parsedEnv)) {
            $eggResponse = Http::withHeaders($this->_headers())->get($this->_url("/nests/{$nestId}/eggs/{$eggId}?include=variables"));
            
            if (!$eggResponse->successful()) {
                throw new ProvisioningException('Failed to fetch Pterodactyl Egg details to populate optional fields. HTTP Status: ' . $eggResponse->status(), ['response' => $eggResponse->json() ?: $eggResponse->body()]);
            }

            $eggData = $eggResponse->json('attributes', []);
            
            if (empty($dockerImage)) $dockerImage = $eggData['docker_image'] ?? '';
            if (empty($startup)) $startup = $eggData['startup'] ?? '';
            
            if (empty($parsedEnv)) {
                $variables = $eggResponse->json('attributes.relationships.variables.data', []);
                foreach ($variables as $var) {
                    $envKey = $var['attributes']['env_variable'];
                    $envDefault = $var['attributes']['default_value'];
                    $parsedEnv[$envKey] = $envDefault;
                }
            }
        }

        if (is_array($parsedEnv)) {
            $environment = array_merge($environment, $parsedEnv);
        }

        // 3. Prepare locations array
        $locationsRaw = $config['location_id'] ?? $config['location_ids'] ?? '1';
        $locations = array_values(array_filter(array_map('intval', explode(',', $locationsRaw)), fn($id) => $id >= 1));

        $serverName = $clientInput['server_name'] ?? 'Billmora Server #' . $service->id;

        $payload = [
            'name' => $serverName,
            'external_id' => (string) $service->id,
            'user' => $pterodactylUserId,
            'egg' => $eggId,
            'docker_image' => $dockerImage,
            'startup' => $startup,
            'environment' => $environment,
            'oom_disabled' => filter_var($config['oom_killer'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'limits' => [
                'memory' => (int) $config['memory'],
                'swap'   => (int) $config['swap'],
                'disk'   => (int) $config['disk'],
                'io'     => (int) $config['io'],
                'cpu'    => (int) $config['cpu'],
            ],
            'feature_limits' => [
                'databases'   => (int) $config['databases'],
                'backups'     => (int) $config['backups'],
                'allocations' => (int) $config['allocations'],
            ],
            'deploy' => [
                'locations'    => $locations,
                'dedicated_ip' => filter_var($config['dedicated_ip'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'port_range'   => !empty($config['port_range']) ? explode(',', $config['port_range']) : [],
            ],
            'start_on_completion' => true,
        ];

        $response = Http::withHeaders($this->_headers())->post($this->_url('/servers'), $payload);

        if (!$response->successful()) {
            throw new ProvisioningException('Failed to create server on Pterodactyl. HTTP Status: ' . $response->status(), ['response' => $response->json() ?: $response->body()]);
        }

        // No need to store the internal serverId locally. 
        // We will resolve it dynamically via external_id ($service->id) when needed.
    }

    private function _getOrCreateUser($user): int
    {
        // Check if user exists by email
        $response = Http::withHeaders($this->_headers())
            ->get($this->_url('/users?filter[email]=' . urlencode($user->email)));

        if ($response->successful() && !empty($response->json('data'))) {
            return $response->json('data.0.attributes.id');
        }

        // Generate username (e.g. from email prefix if username is not alphanumeric/empty)
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $user->email)[0] . random_int(10, 99));

        // Create new user
        $createResponse = Http::withHeaders($this->_headers())->post($this->_url('/users'), [
            'external_id' => (string) $user->id,
            'email'      => $user->email,
            'username'   => $username,
            'first_name' => $user->fullname ?? 'Billmora',
            'last_name'  => 'Client',
        ]);

        if (!$createResponse->successful()) {
            throw new ProvisioningException('Failed to create Pterodactyl user. HTTP Status: ' . $createResponse->status(), ['response' => $createResponse->json() ?: $createResponse->body()]);
        }

        return $createResponse->json('attributes.id');
    }

    public function suspend(Service $service): void
    {
        $serverId = $this->_resolveInternalIdByExternalId($service);

        $response = Http::withHeaders($this->_headers())
            ->post($this->_url('/servers/' . $serverId . '/suspend'));

        if (!$response->successful() && $response->status() !== 404) {
            throw new ProvisioningException('Failed to suspend Pterodactyl server. HTTP Status: ' . $response->status(), ['response' => $response->json() ?: $response->body()]);
        }
    }

    public function unsuspend(Service $service): void
    {
        $serverId = $this->_resolveInternalIdByExternalId($service);

        $response = Http::withHeaders($this->_headers())
            ->post($this->_url('/servers/' . $serverId . '/unsuspend'));

        if (!$response->successful() && $response->status() !== 404) {
            throw new ProvisioningException('Failed to unsuspend Pterodactyl server. HTTP Status: ' . $response->status(), ['response' => $response->json() ?: $response->body()]);
        }
    }

    public function terminate(Service $service): void
    {
        $serverId = $this->_resolveInternalIdByExternalId($service);

        $response = Http::withHeaders($this->_headers())
            ->delete($this->_url('/servers/' . $serverId));

        if (!$response->successful() && $response->status() !== 404) {
            throw new ProvisioningException('Failed to terminate Pterodactyl server. HTTP Status: ' . $response->status(), ['response' => $response->json() ?: $response->body()]);
        }
    }

    public function renew(Service $service): void
    {
        // No action required on Pterodactyl side for renewal (billing only)
    }

    public function scale(Service $service, array $newConfig): void
    {
        $serverId = $this->_resolveInternalIdByExternalId($service);

        $config = $service->package->provisioning_config ?? [];
        $config = $this->_mergeVariants($service, $config);

        if (!is_array($config) || empty($config['egg_id'])) {
            throw new ProvisioningException('Scale Failed: Pterodactyl Package Configuration is missing or invalid.');
        }

        // 1. Fetch existing server to get the primary allocation ID
        $getServerResponse = Http::withHeaders($this->_headers())
            ->get($this->_url('/servers/' . $serverId));

        if (!$getServerResponse->successful()) {
            throw new ProvisioningException('Failed to fetch existing server for scaling. HTTP: ' . $getServerResponse->status(), ['response' => $getServerResponse->json() ?: $getServerResponse->body()]);
        }

        $allocationId = $getServerResponse->json('attributes.allocation');

        // 2. Send Build Config updates
        $buildPayload = [
            'allocation' => $allocationId,
            'memory' => (int) $config['memory'],
            'swap'   => (int) $config['swap'],
            'disk'   => (int) $config['disk'],
            'io'     => (int) $config['io'],
            'cpu'    => (int) $config['cpu'],
            'threads' => null,
            'oom_disabled' => filter_var($config['oom_killer'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'feature_limits' => [
                'databases'   => (int) $config['databases'],
                'backups'     => (int) $config['backups'],
                'allocations' => (int) $config['allocations'],
            ],
        ];

        $buildResponse = Http::withHeaders($this->_headers())
            ->patch($this->_url('/servers/' . $serverId . '/build'), $buildPayload);

        if (!$buildResponse->successful()) {
            throw new ProvisioningException('Failed to scale Pterodactyl server build limits. HTTP Status: ' . $buildResponse->status(), ['response' => $buildResponse->json() ?: $buildResponse->body()]);
        }

        // 3. Send Startup updates (if changed)
        $eggId = (int) $config['egg_id'];
        $nestId = (int) $config['nest_id'];
        $dockerImage = $config['docker_image'] ?? '';
        $startup = $config['startup'] ?? '';
        $environment = ['P_SERVER_ALLOCATION_LIMIT' => (int)($config['allocations'] ?? 0)];
        $parsedEnv = !empty($config['environment']) ? json_decode($config['environment'], true) : [];

        if (empty($dockerImage) || empty($startup) || empty($parsedEnv)) {
            $eggResponse = Http::withHeaders($this->_headers())->get($this->_url("/nests/{$nestId}/eggs/{$eggId}?include=variables"));
            if ($eggResponse->successful()) {
                $eggData = $eggResponse->json('attributes', []);
                if (empty($dockerImage)) $dockerImage = $eggData['docker_image'] ?? '';
                if (empty($startup)) $startup = $eggData['startup'] ?? '';
                if (empty($parsedEnv)) {
                    $variables = $eggResponse->json('attributes.relationships.variables.data', []);
                    foreach ($variables as $var) {
                        $parsedEnv[$var['attributes']['env_variable']] = $var['attributes']['default_value'];
                    }
                }
            }
        }
        
        if (is_array($parsedEnv)) {
            $environment = array_merge($environment, $parsedEnv);
        }

        $startupPayload = [
            'startup' => $startup,
            'environment' => $environment,
            'egg' => $eggId,
            'image' => $dockerImage,
            'skip_scripts' => false,
        ];

        $startupResponse = Http::withHeaders($this->_headers())
            ->patch($this->_url('/servers/' . $serverId . '/startup'), $startupPayload);

        if (!$startupResponse->successful()) {
            throw new ProvisioningException('Failed to scale Pterodactyl server startup variables. HTTP Status: ' . $startupResponse->status(), ['response' => $startupResponse->json() ?: $startupResponse->body()]);
        }
    }

    /**
     * Resolve Pterodactyl server attributes using the Billmora Service ID (external_id).
     * This keeps the Billmora database clean of non-billing technical IDs.
     *
     * @return array<string, mixed> The server attributes from Pterodactyl API.
     */
    private function _resolveServerByExternalId(Service $service): array
    {
        $response = Http::withHeaders($this->_headers())
            ->get($this->_url('/servers/external/' . $service->id));

        if (!$response->successful()) {
            throw new ProvisioningException('Action Aborted: Pterodactyl could not find a server linked to this Service ID (#' . $service->id . ').', ['response' => $response->json() ?: $response->body()]);
        }

        return $response->json('attributes', []);
    }

    /**
     * Resolve Pterodactyl's Internal Server ID using the Billmora Service ID (external_id).
     */
    private function _resolveInternalIdByExternalId(Service $service): int
    {
        return (int) $this->_resolveServerByExternalId($service)['id'];
    }

    private function _mergeVariants(Service $service, array $config): array
    {
        if (empty($service->variant_selections)) {
            return $config;
        }

        $optionIds = collect($service->variant_selections)->flatten()->filter()->toArray();
        if (empty($optionIds)) {
            return $config;
        }

        $options = \App\Models\VariantOption::with('variant')->whereIn('id', $optionIds)->get();

        foreach ($options as $option) {
            if ($option->variant && $option->variant->code) {
                // If the user's variant code matches our schema dictionary, inject it into the payload.
                $config[$option->variant->code] = $option->value;
            }
        }

        return $config;
    }

    public function getClientAction(Service $service): array
    {
        return [
            'panel' => [
                'label' => 'Go to Control Panel',
                'icon'  => 'fa-solid fa-server',
                'type'  => 'link',
            ],
        ];
    }

    public function handleClientAction(Service $service, string $slug, array $data = [])
    {
        if ($slug === 'panel') {
            $server = $this->_resolveServerByExternalId($service);
            $shortUuid = $server['identifier'];

            return redirect()->away(rtrim($this->getInstanceConfig('panel_url'), '/') . '/server/' . $shortUuid);
        }

        throw new \Exception("Unknown action requested: {$slug}");
    }
}
