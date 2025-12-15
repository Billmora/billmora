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
}
