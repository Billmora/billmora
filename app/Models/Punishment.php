<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Punishment extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'status',
        'expires_at',
        'terminate_services',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'terminate_services' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
