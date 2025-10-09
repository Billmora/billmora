<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currencies extends Model
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
        'is_default' => 'boolean',
        'base_rate' => 'decimal:8',
    ];
}
