<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provisioning extends Model
{
    public const ERROR_DISABLED = 801;

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
     * Get the services for this provisioning.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the plugin instance for this provisioning driver.
     *
     * @return \App\Contracts\ProvisioningInterface|null
     */
    public function getPluginInstance()
    {
        if (!$this->is_active) {
            throw new \Exception(__('validation.provisioning_disabled', ['name' => $this->name]), self::ERROR_DISABLED);
        }

        $className = "Plugin\\Provisioning\\{$this->driver}\\{$this->driver}";

        if (class_exists($className)) {
            return new $className();
        }

        return null;
    }
}
