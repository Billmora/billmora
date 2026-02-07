<?php

namespace App\Models;

use App\Contracts\ProvisioningInterface;
use Illuminate\Database\Eloquent\Model;

class Provisioning extends Model
{
    protected $table = 'provisionings';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var list<string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array', 
    ];

    /**
     * Get the plugin instance for this provisioning driver.
     *
     * @return \App\Contracts\ProvisioningInterface|null
     */
    public function getPluginInstance()
    {
        $className = "Plugin\\Provisioning\\{$this->driver}\\{$this->driver}";

        if (class_exists($className)) {
            return new $className();
        }

        return null;
    }
}
