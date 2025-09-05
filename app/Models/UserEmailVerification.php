<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEmailVerification extends Model
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
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Check if the token has expired.
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the token has been verified.
     */
    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    /**
     * Verify the token and mark it as verified.
     */
    public function markAsVerified()
    {
        $this->update([
            'verified_at' => now(),
        ]);

        $this->user->update([
            'email_verified_at' => now(),
        ]);
    }
}
