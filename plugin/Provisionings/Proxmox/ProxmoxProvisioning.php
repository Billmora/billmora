<?php

namespace Plugins\Provisionings\Proxmox;

use App\Contracts\ProvisioningInterface;
use App\Exceptions\ProvisioningException;
use App\Models\Service;
use App\Support\AbstractPlugin;
use Illuminate\Support\Facades\Http;
use Plugins\Provisionings\Proxmox\Jobs\ProxmoxSetupJob;

class ProxmoxProvisioning extends AbstractPlugin implements ProvisioningInterface
{
    private function _url(string $path): string
    {
        return rtrim($this->getInstanceConfig('host'), '/') . '/api2/json' . $path;
    }

    private function _headers(): array
    {
        $tokenId     = $this->getInstanceConfig('api_token_id');
        $tokenSecret = $this->getInstanceConfig('api_token_secret');

        return [
            'Authorization' => "PVEAPIToken={$tokenId}={$tokenSecret}",
        ];
    }

    /**
     * JSON body client — used for create, config, resize.
     */
    private function _http(): \Illuminate\Http\Client\PendingRequest
    {
        $verify = (bool) $this->getInstanceConfig('verify_ssl', true);

        return Http::withHeaders($this->_headers())
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withOptions(['verify' => $verify]);
    }

    /**
     * Form-encoded client — used for power actions (start, stop, reset).
     * Proxmox returns "Not a HASH reference" when JSON Content-Type is sent to these endpoints.
     */
    private function _httpForm(): \Illuminate\Http\Client\PendingRequest
    {
        $verify = (bool) $this->getInstanceConfig('verify_ssl', true);

        return Http::withHeaders($this->_headers())
            ->asForm()
            ->withOptions(['verify' => $verify]);
    }

    /**
     * Resolve target node from package config, or auto-select the node with the most free memory.
     */
    private function _resolveNode(array $config): string
    {
        if (!empty($config['node'])) {
            return trim($config['node']);
        }

        $response = $this->_http()->get($this->_url('/nodes'));

        if (!$response->successful()) {
            throw new ProvisioningException(
                'Failed to list Proxmox nodes for auto-selection.',
                ['response' => $response->json() ?: $response->body()]
            );
        }

        $node = collect($response->json('data', []))
            ->filter(fn($n) => ($n['status'] ?? '') === 'online')
            ->sortByDesc(fn($n) => $n['maxmem'] - $n['mem'])
            ->first();

        if (!$node) {
            throw new ProvisioningException('No online Proxmox nodes available for auto-selection.');
        }

        return $node['node'];
    }

    private function _nextVmId(): int
    {
        $response = $this->_http()->get($this->_url('/cluster/nextid'));

        if (!$response->successful()) {
            throw new ProvisioningException(
                'Failed to retrieve next available VMID from Proxmox.',
                ['response' => $response->json() ?: $response->body()]
            );
        }

        return (int) $response->json('data');
    }

    /**
     * Poll a Proxmox task UPID until it completes or times out.
     */
    private function _waitForTask(string $node, string $upid, int $maxSeconds = 120): void
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        $encodedUpid = urlencode($upid);
        $elapsed     = 0;
        $interval    = 3;

        while ($elapsed < $maxSeconds) {
            sleep($interval);
            $elapsed += $interval;

            $response = $this->_http()->get($this->_url("/nodes/{$node}/tasks/{$encodedUpid}/status"));

            if (!$response->successful()) {
                continue;
            }

            $data   = $response->json('data', []);
            $status = $data['status'] ?? '';

            if ($status === 'stopped') {
                $exitStatus = $data['exitstatus'] ?? 'unknown';
                if ($exitStatus !== 'OK') {
                    throw new ProvisioningException(
                        "Proxmox task failed with exit status: {$exitStatus}.",
                        ['upid' => $upid, 'data' => $data]
                    );
                }
                return;
            }
        }

        throw new ProvisioningException(
            "Proxmox task timed out after {$maxSeconds} seconds.",
            ['upid' => $upid]
        );
    }

    /**
     * @return array{vmid: int, node: string}
     */
    private function _resolveVmFromService(Service $service): array
    {
        $configuration = $service->configuration ?? [];
        $vmid          = $configuration['proxmox_vmid'] ?? null;
        $node          = $configuration['proxmox_node'] ?? null;

        if (!$vmid || !$node) {
            throw new ProvisioningException(
                "Action Aborted: No Proxmox VM is linked to Service #{$service->id}. " .
                "The VM may not have been created yet, or the service configuration is missing."
            );
        }

        return ['vmid' => (int) $vmid, 'node' => (string) $node];
    }

    private function _getVmStatus(int $vmId, string $node): string
    {
        $response = $this->_http()->get($this->_url("/nodes/{$node}/qemu/{$vmId}/status/current"));

        if (!$response->successful()) {
            return 'unknown';
        }

        return $response->json('data.status', 'unknown');
    }

    /**
     * Merge variant option values into config using variant codes as keys.
     */
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
                $config[$option->variant->code] = $option->value;
            }
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigSchema(): array
    {
        return [
            'host' => [
                'type'        => 'url',
                'label'       => 'Proxmox Host URL',
                'placeholder' => 'https://proxmox.example.com:8006',
                'helper'      => 'Include the port (default 8006). Example: https://192.168.1.10:8006',
                'rules'       => 'required|url',
            ],
            'api_token_id' => [
                'type'        => 'text',
                'label'       => 'API Token ID',
                'placeholder' => 'root@pam!billmora',
                'helper'      => 'Format: user@realm!tokenid (e.g. root@pam!billmora). The token must have VM.Allocate, VM.Clone, VM.Config.*, and Datastore.AllocateSpace privileges.',
                'rules'       => 'required|string',
            ],
            'api_token_secret' => [
                'type'   => 'password',
                'label'  => 'API Token Secret',
                'helper' => 'The UUID secret generated when you created the API token in Proxmox.',
                'rules'  => 'required|string',
            ],
            'verify_ssl' => [
                'type'    => 'toggle',
                'label'   => 'Verify SSL Certificate',
                'helper'  => 'Disable only for self-signed certificates in development environments.',
                'default' => true,
                'rules'   => 'boolean',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageSchema(): array
    {
        return [
            'node' => [
                'type'   => 'text',
                'label'  => 'Target Node',
                'helper' => 'The Proxmox node name to deploy VMs on (e.g. pve). Leave empty to auto-select the node with the most free memory.',
                'rules'  => 'nullable|string|max:63',
            ],
            'template_vmid' => [
                'type'   => 'number',
                'label'  => 'Template VMID',
                'helper' => 'The VM ID of the Cloud-Init enabled template to clone for each order.',
                'rules'  => 'required|integer|min:100',
            ],
            'storage' => [
                'type'        => 'text',
                'label'       => 'Target Storage',
                'placeholder' => 'local-lvm',
                'helper'      => 'The storage where the cloned VM disk will be placed (e.g. local-lvm, ceph-pool).',
                'rules'       => 'required|string|max:100',
            ],
            'network_bridge' => [
                'type'        => 'text',
                'label'       => 'Network Bridge',
                'placeholder' => 'vmbr0',
                'helper'      => 'The Proxmox network bridge to attach the VM\'s network interface to.',
                'default'     => 'vmbr0',
                'rules'       => 'required|string|max:20',
            ],
            'cores' => [
                'type'    => 'number',
                'label'   => 'CPU Cores',
                'helper'  => 'Number of CPU cores to allocate to the VM.',
                'default' => 1,
                'rules'   => 'required|integer|min:1',
            ],
            'memory' => [
                'type'    => 'number',
                'label'   => 'Memory (MB)',
                'helper'  => 'RAM in MB to allocate to the VM.',
                'default' => 1024,
                'rules'   => 'required|integer|min:256',
            ],
            'disk_size' => [
                'type'    => 'number',
                'label'   => 'Disk Size (GB)',
                'helper'  => 'Root disk size in GB. The template disk will be resized to this value.',
                'default' => 20,
                'rules'   => 'required|integer|min:1',
            ],
            'bandwidth' => [
                'type'    => 'number',
                'label'   => 'Bandwidth Limit (GB)',
                'helper'  => 'Monthly bandwidth limit in GB. Set to 0 for unlimited.',
                'default' => 0,
                'rules'   => 'required|integer|min:0',
            ],
            'os_templates' => [
                'type'   => 'textarea',
                'label'  => 'OS Templates (JSON)',
                'helper' => 'Define available OS options for client reinstall. Each key is the display name, value is the Proxmox VMID of the template. Example: {"Ubuntu 22.04": 201, "Debian 12": 202}. Leave empty to use the default template only.',
                'rules'  => 'nullable|json',
            ],
            'start_on_create' => [
                'type'    => 'toggle',
                'label'   => 'Start VM After Creation',
                'default' => true,
                'rules'   => 'boolean',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckoutSchema(): array
    {
        return [
            'root_password' => [
                'type'   => 'password',
                'label'  => 'Root Password',
                'helper' => 'Set the root/administrator password for your VM. Minimum 8 characters.',
                'rules'  => 'required|string|min:8|max:72',
            ],
            'hostname' => [
                'type'        => 'text',
                'label'       => 'Hostname',
                'placeholder' => 'my-vps',
                'helper'      => 'Optional. Hostname for your VM. Defaults to your service ID if left empty.',
                'rules'       => 'nullable|string|max:63|regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?$/',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection(array $config): bool
    {
        $verify      = (bool) ($config['verify_ssl'] ?? true);
        $tokenId     = $config['api_token_id'] ?? '';
        $tokenSecret = $config['api_token_secret'] ?? '';

        $url = rtrim($config['host'], '/') . '/api2/json/nodes';

        $response = Http::withHeaders([
            'Authorization' => "PVEAPIToken={$tokenId}={$tokenSecret}",
            'Content-Type'  => 'application/json',
        ])->withOptions(['verify' => $verify])->get($url);

        if (!$response->successful()) {
            throw new ProvisioningException(
                'Failed to connect to Proxmox VE. HTTP Status: ' . $response->status() .
                '. Please verify the host URL and API token credentials.',
                ['response' => $response->json() ?: $response->body()]
            );
        }

        $nodes = $response->json('data', []);
        if (empty($nodes)) {
            throw new ProvisioningException(
                'Connected to Proxmox, but no nodes were found. ' .
                'Ensure the API token has the required privileges (PVEAdmin or equivalent).'
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Service $service): void
    {
        $config      = $service->package->provisioning_config ?? [];
        $config      = $this->_mergeVariants($service, $config);
        $clientInput = $service->configuration ?? [];

        if (empty($config['template_vmid'])) {
            throw new ProvisioningException(
                'Proxmox package configuration is incomplete. ' .
                'Please set a Template VMID in the package settings.'
            );
        }

        $node          = $this->_resolveNode($config);
        $templateId    = (int) $config['template_vmid'];
        $hostname      = $clientInput['hostname'] ?? ('billmora-' . $service->id);
        $password      = $clientInput['root_password'] ?? null;
        $storage       = $config['storage'] ?? 'local-lvm';
        $cores         = (int) ($config['cores'] ?? 1);
        $memory        = (int) ($config['memory'] ?? 1024);
        $diskSize      = (int) ($config['disk_size'] ?? 20);
        $bridge        = $config['network_bridge'] ?? 'vmbr0';
        $startOnCreate = filter_var($config['start_on_create'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $vmId = $this->_nextVmId();

        $cloneResponse = $this->_http()->post(
            $this->_url("/nodes/{$node}/qemu/{$templateId}/clone"),
            ['newid' => $vmId, 'name' => $hostname, 'storage' => $storage, 'full' => 1]
        );

        if (!$cloneResponse->successful()) {
            throw new ProvisioningException(
                "Failed to clone Proxmox template (VMID {$templateId}). HTTP Status: " . $cloneResponse->status(),
                ['response' => $cloneResponse->json() ?: $cloneResponse->body()]
            );
        }

        // Persist VMID immediately so subsequent actions work even if the setup job times out.
        $configuration                 = $service->configuration ?? [];
        $configuration['proxmox_vmid'] = $vmId;
        $configuration['proxmox_node'] = $node;
        $service->update(['configuration' => $configuration]);

        $configPayload = [
            'cores'  => $cores,
            'memory' => $memory,
            'net0'   => "virtio,bridge={$bridge}",
            'ciuser' => 'root',
            'agent'  => '1',
        ];

        $ipv4        = $clientInput['ipv4_address'] ?? null;
        $ipv4Netmask = $clientInput['ipv4_netmask'] ?? '24';
        $ipv4Gateway = $clientInput['ipv4_gateway'] ?? null;

        if ($ipv4) {
            $ipconfig = "ip={$ipv4}/{$ipv4Netmask}";
            if ($ipv4Gateway) {
                $ipconfig .= ",gw={$ipv4Gateway}";
            }
            $configPayload['ipconfig0']  = $ipconfig;
            $configPayload['nameserver'] = '1.1.1.1 8.8.8.8';
        } else {
            $configPayload['ipconfig0'] = 'ip=dhcp';
        }

        if (!empty($password)) {
            $configPayload['cipassword'] = $password;
        }

        // Dispatch heavy setup work to a queue job to avoid a 504 Gateway Timeout.
        ProxmoxSetupJob::dispatch(
            serviceId:      $service->id,
            vmId:           $vmId,
            node:           $node,
            cloneUpid:      $cloneResponse->json('data'),
            config:         $config,
            configPayload:  $configPayload,
            startOnCreate:  $startOnCreate,
            instanceConfig: $this->instanceConfig,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function suspend(Service $service): void
    {
        ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);

        $response = $this->_httpForm()->post(
            $this->_url("/nodes/{$node}/qemu/{$vmId}/status/stop")
        );

        if (!$response->successful() && $response->status() !== 500) {
            throw new ProvisioningException(
                "Failed to suspend VM {$vmId}. HTTP Status: " . $response->status(),
                ['response' => $response->json() ?: $response->body()]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unsuspend(Service $service): void
    {
        ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);

        $response = $this->_httpForm()->post(
            $this->_url("/nodes/{$node}/qemu/{$vmId}/status/start")
        );

        if (!$response->successful()) {
            throw new ProvisioningException(
                "Failed to unsuspend VM {$vmId}. HTTP Status: " . $response->status(),
                ['response' => $response->json() ?: $response->body()]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(Service $service): void
    {
        ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);

        if ($this->_getVmStatus($vmId, $node) === 'running') {
            $stopResponse = $this->_httpForm()->post($this->_url("/nodes/{$node}/qemu/{$vmId}/status/stop"));
            if ($stopResponse->successful()) {
                $stopUpid = $stopResponse->json('data');
                if ($stopUpid) {
                    $this->_waitForTask($node, $stopUpid);
                }
            }
        }

        // Purge params must be in the query string; DELETE with a JSON body causes
        // Proxmox to throw "Not a HASH reference".
        $deleteUrl = $this->_url("/nodes/{$node}/qemu/{$vmId}") . '?purge=1';
        $response  = $this->_http()->delete($deleteUrl);

        $body = $response->body();
        if (!$response->successful() && $response->status() !== 404 && !str_contains($body, 'does not exist')) {
            throw new ProvisioningException(
                "Failed to delete VM {$vmId} from Proxmox. HTTP Status: " . $response->status(),
                ['response' => $response->json() ?: $body]
            );
        }
    }

    /**
     * Expose _waitForTask() for use by queue jobs.
     */
    public function waitForTaskPublic(string $node, string $upid): void
    {
        $this->_waitForTask($node, $upid);
    }

    /**
     * Apply a Cloud-Init or hardware config payload to a VM.
     */
    public function configureVmPublic(string $node, int $vmId, array $payload): void
    {
        $response = $this->_http()->post(
            $this->_url("/nodes/{$node}/qemu/{$vmId}/config"),
            $payload
        );

        if (!$response->successful()) {
            // Non-fatal: config errors are surfaced when the VM behaves unexpectedly.
        }
    }

    /**
     * Resize the primary disk of a VM.
     */
    public function resizeDiskPublic(string $node, int $vmId, int $diskSizeGb): void
    {
        $response = $this->_http()->put(
            $this->_url("/nodes/{$node}/qemu/{$vmId}/resize"),
            ['disk' => 'scsi0', 'size' => "{$diskSizeGb}G"]
        );

    }

    /**
     * Start a VM.
     */
    public function startVmPublic(string $node, int $vmId): void
    {
        $this->_httpForm()->post(
            $this->_url("/nodes/{$node}/qemu/{$vmId}/status/start")
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renew(Service $service): void
    {
        // No-op: Proxmox VMs do not have an expiry managed at the hypervisor level.
    }

    /**
     * {@inheritdoc}
     */
    public function scale(Service $service, array $newConfig): void
    {
        $config = !empty($newConfig) ? $newConfig : ($service->package->provisioning_config ?? []);
        $config = $this->_mergeVariants($service, $config);

        $cores    = (int) ($config['cores'] ?? 1);
        $memory   = (int) ($config['memory'] ?? 1024);
        $diskSize = (int) ($config['disk_size'] ?? 20);

        ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);

        $configResponse = $this->_http()->post(
            $this->_url("/nodes/{$node}/qemu/{$vmId}/config"),
            ['cores' => $cores, 'memory' => $memory]
        );

        if (!$configResponse->successful()) {
            throw new ProvisioningException(
                "Failed to scale VM {$vmId} CPU/Memory. HTTP Status: " . $configResponse->status(),
                ['response' => $configResponse->json() ?: $configResponse->body()]
            );
        }

        // Proxmox only allows disk growth, never shrinking.
        if ($diskSize > 0) {
            $this->_http()->put(
                $this->_url("/nodes/{$node}/qemu/{$vmId}/resize"),
                ['disk' => 'scsi0', 'size' => "{$diskSize}G"]
            );
        }

        if ($this->_getVmStatus($vmId, $node) === 'running') {
            // Proxmox requires the KVM process to restart to apply CPU/Memory changes.
            $this->_httpForm()->post($this->_url("/nodes/{$node}/qemu/{$vmId}/status/stop"));
            sleep(3);
            $this->_httpForm()->post($this->_url("/nodes/{$node}/qemu/{$vmId}/status/start"));
        }
    }

    /**
     * Build OS reinstall options from the package's os_templates config.
     *
     * @return array{options: array<string,string>, rule: string}
     */
    private function _buildOsOptions(Service $service): array
    {
        $config       = $service->package->provisioning_config ?? [];
        $rawTemplates = $config['os_templates'] ?? '';
        $defaultVmId  = (int) ($config['template_vmid'] ?? 0);

        $parsed = !empty($rawTemplates) ? json_decode($rawTemplates, true) : null;

        if (is_array($parsed) && !empty($parsed)) {
            $options  = [];
            foreach ($parsed as $label => $vmid) {
                $options[(string) $vmid] = $label;
            }
            $validIds = implode(',', array_keys($options));
            return ['options' => $options, 'rule' => "required|in:{$validIds}"];
        }

        $label   = 'Default Template (VMID ' . $defaultVmId . ')';
        $options = [(string) $defaultVmId => $label];
        return ['options' => $options, 'rule' => "required|in:{$defaultVmId}"];
    }

    /**
     * Stop the existing VM, delete it, clone a new template with the same VMID, and reconfigure.
     * Called by ProxmoxReinstallJob to run asynchronously.
     */
    public function reinstallVmPublic(Service $service, int $templateId, string $hostname, ?string $password, bool $startOnCreate = true): void
    {
        $config = $service->package->provisioning_config ?? [];
        $config = $this->_mergeVariants($service, $config);
        ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);

        $storage  = $config['storage'] ?? 'local-lvm';
        $cores    = (int) ($config['cores'] ?? 1);
        $memory   = (int) ($config['memory'] ?? 1024);
        $diskSize = (int) ($config['disk_size'] ?? 20);
        $bridge   = $config['network_bridge'] ?? 'vmbr0';

        $statusResponse = $this->_http()->get($this->_url("/nodes/{$node}/qemu/{$vmId}/status/current"));
        if ($statusResponse->successful()) {
            $this->_httpForm()->post($this->_url("/nodes/{$node}/qemu/{$vmId}/status/stop"));
            sleep(3);

            $deleteUrl      = $this->_url("/nodes/{$node}/qemu/{$vmId}") . '?purge=1';
            $deleteResponse = $this->_http()->delete($deleteUrl);

            $body = $deleteResponse->body();
            if (!$deleteResponse->successful() && $deleteResponse->status() !== 404 && !str_contains($body, 'does not exist')) {
                throw new ProvisioningException(
                    "Reinstall failed: could not remove existing VM {$vmId}. HTTP Status: " . $deleteResponse->status(),
                    ['response' => $deleteResponse->json() ?: $body]
                );
            }

            // Wait for deletion to complete before cloning.
            if ($deleteResponse->successful() && $deleteResponse->json('data')) {
                $this->_waitForTask($node, $deleteResponse->json('data'));
            }
        }

        $cloneResponse = $this->_http()->post(
            $this->_url("/nodes/{$node}/qemu/{$templateId}/clone"),
            ['newid' => $vmId, 'name' => $hostname, 'storage' => $storage, 'full' => 1]
        );
        if (!$cloneResponse->successful()) {
            throw new ProvisioningException(
                "Reinstall failed: could not clone template {$templateId}. HTTP Status: " . $cloneResponse->status(),
                ['response' => $cloneResponse->json() ?: $cloneResponse->body()]
            );
        }
        $this->_waitForTask($node, $cloneResponse->json('data'));

        $clientInput   = $service->configuration ?? [];
        $configPayload = [
            'cores'  => $cores,
            'memory' => $memory,
            'net0'   => "virtio,bridge={$bridge}",
            'ciuser' => 'root',
            'agent'  => '1',
        ];

        $ipv4        = $clientInput['ipv4_address'] ?? null;
        $ipv4Netmask = $clientInput['ipv4_netmask'] ?? '24';
        $ipv4Gateway = $clientInput['ipv4_gateway'] ?? null;

        if ($ipv4) {
            $ipconfig = "ip={$ipv4}/{$ipv4Netmask}";
            if ($ipv4Gateway) {
                $ipconfig .= ",gw={$ipv4Gateway}";
            }
            $configPayload['ipconfig0']  = $ipconfig;
            $configPayload['nameserver'] = '1.1.1.1 8.8.8.8';
        } else {
            $configPayload['ipconfig0'] = 'ip=dhcp';
        }

        if (!empty($password)) {
            $configPayload['cipassword'] = $password;
        }

        $this->_http()->post($this->_url("/nodes/{$node}/qemu/{$vmId}/config"), $configPayload);

        if ($diskSize > 0) {
            $this->_http()->put(
                $this->_url("/nodes/{$node}/qemu/{$vmId}/resize"),
                ['disk' => 'scsi0', 'size' => "{$diskSize}G"]
            );
        }

        if ($startOnCreate) {
            $this->_httpForm()->post($this->_url("/nodes/{$node}/qemu/{$vmId}/status/start"));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getClientAction(Service $service): array
    {
        $osOptions = $this->_buildOsOptions($service);

        return [
            'stats' => [
                'label' => 'Summary',
                'icon'  => 'fa-solid fa-circle-info',
                'type'  => 'page',
            ],
            'console' => [
                'label' => 'Open Console',
                'icon'  => 'fa-solid fa-terminal',
                'type'  => 'link',
            ],
            'reboot' => [
                'label'  => 'Reboot',
                'icon'   => 'fa-solid fa-rotate',
                'type'   => 'submit',
                'method' => 'POST',
            ],
            'shutdown' => [
                'label'  => 'Shutdown',
                'icon'   => 'fa-solid fa-power-off',
                'type'   => 'submit',
                'method' => 'POST',
            ],
            'power_on' => [
                'label'  => 'Power On',
                'icon'   => 'fa-solid fa-play',
                'type'   => 'submit',
                'method' => 'POST',
            ],
            'change_password' => [
                'label'  => 'Change Password',
                'icon'   => 'fa-solid fa-key',
                'type'   => 'form',
                'schema' => [
                    'new_password' => [
                        'type'   => 'password',
                        'label'  => 'New Root Password',
                        'helper' => 'The new root password for your VM. Takes effect after the next reboot.',
                        'rules'  => 'required|string|min:8|max:72',
                    ],
                    'new_password_confirmation' => [
                        'type'  => 'password',
                        'label' => 'Confirm New Password',
                        'rules' => 'required|same:new_password',
                    ],
                ],
            ],
            'reinstall' => [
                'label'  => 'Reinstall OS',
                'icon'   => 'fa-solid fa-compact-disc',
                'type'   => 'form',
                'schema' => [
                    'template_vmid' => [
                        'type'    => 'select',
                        'label'   => 'Operating System',
                        'options' => $osOptions['options'],
                        'rules'   => $osOptions['rule'],
                    ],
                    'root_password' => [
                        'type'   => 'password',
                        'label'  => 'New Root Password',
                        'helper' => 'Minimum 8 characters.',
                        'rules'  => 'required|string|min:8|max:72',
                    ],
                    'confirm_wipe' => [
                        'type'  => 'toggle',
                        'label' => 'I understand that all data on this VM will be permanently lost.',
                        'rules' => 'accepted',
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handleClientAction(Service $service, string $slug, array $data = [])
    {
        switch ($slug) {

            case 'stats':
                ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);
                $config = $service->package->provisioning_config ?? [];
                $config = $this->_mergeVariants($service, $config);

                $stats    = [];
                $response = $this->_http()->get($this->_url("/nodes/{$node}/qemu/{$vmId}/status/current"));
                if ($response->successful()) {
                    $stats = $response->json('data', []);
                }

                $ipAddresses   = [];
                $agentResponse = $this->_http()->get($this->_url("/nodes/{$node}/qemu/{$vmId}/agent/network-get-interfaces"));
                if ($agentResponse->successful()) {
                    $interfaces = $agentResponse->json('data.result', []);
                    foreach ($interfaces as $iface) {
                        $name = $iface['name'] ?? '';
                        if ($name === 'lo') continue;
                        foreach ($iface['ip-addresses'] ?? [] as $addr) {
                            $ip   = $addr['ip-address'] ?? '';
                            $type = $addr['ip-address-type'] ?? '';
                            if ($ip && in_array($type, ['ipv4', 'ipv6'])) {
                                $ipAddresses[] = [
                                    'iface' => $name,
                                    'ip'    => $ip,
                                    'type'  => strtoupper($type),
                                ];
                            }
                        }
                    }
                }

                return view('provisioning.proxmox::client.stats', [
                    'service'       => $service,
                    'config'        => $config,
                    'stats'         => $stats,
                    'ipAddresses'   => $ipAddresses,
                    'clientActions' => $this->getClientAction($service),
                ]);

            case 'console':
                ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);
                $host = rtrim($this->getInstanceConfig('host'), '/');
                return redirect()->away("{$host}/?console=kvm&novnc=1&node={$node}&vmid={$vmId}");

            case 'reboot':
                ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);
                if ($this->_getVmStatus($vmId, $node) !== 'running') {
                    return 'VM is not running. Please use Power On to start it first.';
                }
                $response = $this->_httpForm()->post($this->_url("/nodes/{$node}/qemu/{$vmId}/status/reset"));
                if (!$response->successful()) {
                    throw new ProvisioningException(
                        "Failed to reboot VM {$vmId}. HTTP Status: " . $response->status(),
                        ['response' => $response->json() ?: $response->body()]
                    );
                }
                return null;

            case 'shutdown':
                ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);
                if ($this->_getVmStatus($vmId, $node) === 'stopped') {
                    return 'VM is already powered off.';
                }
                $response = $this->_httpForm()->post($this->_url("/nodes/{$node}/qemu/{$vmId}/status/stop"));
                if (!$response->successful()) {
                    throw new ProvisioningException(
                        "Failed to power off VM {$vmId}. HTTP Status: " . $response->status(),
                        ['response' => $response->json() ?: $response->body()]
                    );
                }
                return null;

            case 'power_on':
                ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);
                if ($this->_getVmStatus($vmId, $node) === 'running') {
                    return 'VM is already running.';
                }
                $response = $this->_httpForm()->post($this->_url("/nodes/{$node}/qemu/{$vmId}/status/start"));
                if (!$response->successful()) {
                    throw new ProvisioningException(
                        "Failed to power on VM {$vmId}. HTTP Status: " . $response->status(),
                        ['response' => $response->json() ?: $response->body()]
                    );
                }
                return null;

            case 'change_password':
                ['vmid' => $vmId, 'node' => $node] = $this->_resolveVmFromService($service);
                $newPassword = $data['new_password'];

                // Try Guest Agent first for instant effect without a reboot.
                $agentResponse = $this->_http()->post(
                    $this->_url("/nodes/{$node}/qemu/{$vmId}/agent/set-user-password"),
                    ['password' => $newPassword, 'username' => 'root']
                );

                // Always update Cloud-Init config so the password persists on future reinstalls.
                $configResponse = $this->_http()->post(
                    $this->_url("/nodes/{$node}/qemu/{$vmId}/config"),
                    ['cipassword' => $newPassword]
                );

                if ($agentResponse->successful()) {
                    return 'Password has been updated instantly. No reboot is required.';
                }

                if (!$configResponse->successful()) {
                    throw new ProvisioningException(
                        "Failed to update password configuration for VM {$vmId}.",
                        ['response' => $configResponse->json() ?: $configResponse->body()]
                    );
                }

                return 'Password configuration updated. Note: Because the VM is off or the Guest Agent is not running, the password could not be applied instantly. It will apply on the next fresh install.';

            case 'reinstall':
                $templateId    = (int) $data['template_vmid'];
                $password      = $data['root_password'];
                $hostname      = $service->configuration['hostname'] ?? ('billmora-' . $service->id);
                $startOnCreate = (bool) ($service->package->provisioning_config['start_on_create'] ?? true);

                \Plugins\Provisionings\Proxmox\Jobs\ProxmoxReinstallJob::dispatch(
                    $service->id,
                    $templateId,
                    $hostname,
                    $password,
                    $startOnCreate,
                    $this->getInstanceConfig()
                );

                return 'Your VM is being reinstalled. This may take a few minutes.';

            default:
                throw new \Exception("Unknown Proxmox client action requested: {$slug}");
        }
    }
}
