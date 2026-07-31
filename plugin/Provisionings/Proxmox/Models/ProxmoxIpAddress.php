<?php

namespace Plugins\Provisionings\Proxmox\Models;

use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

class ProxmoxIpAddress extends Model
{
    protected $table = 'pp_proxmox_ip_addresses';

    protected $fillable = [
        'ip_pool_id',
        'ip_address',
        'status',
        'service_id',
    ];

    public function pool()
    {
        return $this->belongsTo(ProxmoxIpPool::class, 'ip_pool_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
