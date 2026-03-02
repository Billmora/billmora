<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
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
        'status' => 'string',
        'allow_cancellation' => 'boolean',
        'provisioning_config' => 'array',
    ];

    /**
     * Get the catalog that this package belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function catalog()
    {
        return $this->belongsTo(Catalog::class);
    }

    /**
     * Get the variants associated with this package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function variants()
    {
        return $this->belongsToMany(Variant::class)->withTimestamps();
    }

    /**
     * Get all package prices that belong to this package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices()
    {
        return $this->hasMany(PackagePrice::class);
    }

    /**
     * Get the primary (default) price for the package.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function primaryPrice(): Attribute
    {
        return Attribute::make(fn () => $this->prices->sortBy('id')->first());
    }

    /**
     * Get all packages that this package can be scaled up or down to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function scalablePackages()
    {
        return $this->belongsToMany(
            Package::class, 
            'package_scalings',
            'package_id',
            'target_package_id'
        )->withTimestamps();
    }

    /**
     * Get the coupons associated with this package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class);
    }

    /**
     * Get the orders for this package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the services for this package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the plugin instance associated with the package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }

    /**
     * Get the provisioning plugin relationship scoped by provisioning type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provisioning()
    {
        return $this->plugin()->where('type', 'provisioning');
    }
}
