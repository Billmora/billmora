<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTwoFactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'secret_key',
        'recovery_codes',
        'enabled',
        'downloaded',
    ];

    protected $casts = [
        'recovery_codes' => 'array',
        'enabled' => 'boolean',
        'downloaded' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
