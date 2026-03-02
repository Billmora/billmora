<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageScaling extends Model
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
     * Get the source package associated with the scaling configuration.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Get the target package to which the scaling configuration points.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function targetPackage()
    {
        return $this->belongsTo(Package::class, 'target_package_id');
    }
}
