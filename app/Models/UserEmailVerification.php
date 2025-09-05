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
     * Determine if the verification token is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the email verification has already been completed.
     *
     * @return bool
     */
    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    /**
     * Mark the email verification as completed.
     *
     * @return void
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
