<?php

namespace Plugins\Provisionings\Proxmox\Models;

use App\Models\Plugin;
use Illuminate\Database\Eloquent\Model;

class ProxmoxIpPool extends Model
{
    protected $table = 'pp_proxmox_ip_pools';

    protected $fillable = [
        'plugin_id',
        'name',
        'ip_version',
        'subnet',
        'gateway',
        'netmask',
        'prefix_length',
        'nameservers',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function instance()
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }

    public function ips()
    {
        return $this->hasMany(ProxmoxIpAddress::class, 'ip_pool_id');
    }
}
