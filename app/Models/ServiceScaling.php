<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceScaling extends Model
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'variant_selections' => 'array',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the service associated with the scaling record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the invoice generated for the scaling transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    
    /**
     * Get the package assigned to the service before the scaling occurred.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function oldPackage()
    {
        return $this->belongsTo(Package::class, 'old_package_id');
    }

    /**
     * Get the package assigned to the service after the scaling occurred.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function newPackage()
    {
        return $this->belongsTo(Package::class, 'new_package_id');
    }
}
