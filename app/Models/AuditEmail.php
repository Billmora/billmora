<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditEmail extends Model
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
        'properties' => 'array',
    ];
}
