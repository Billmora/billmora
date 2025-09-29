<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPasswordReset extends Model
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
     * Get the user associated with this password reset request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if the password reset token is still active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Determine if the password reset token has expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Determine if the password reset token has already been used (verified).
     *
     * @return bool
     */
    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    /**
     * Mark the password reset token as verified (used).
     *
     * @return void
     */
    public function markAsVerified()
    {
        $this->update([
            'verified_at' => now(),
        ]);
    }
}
