<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTwoFactor extends Model
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
        'recovery_codes' => 'array',
        'enabled' => 'boolean',
        'downloaded' => 'boolean',
    ];

    /**
     * Get the user that owns the two-factor authentication settings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if two-factor authentication is currently active for the user.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->enabled;
    }

    /**
     * Determine if the two-factor recovery codes have been downloaded by the user.
     *
     * @return bool
     */
    public function isDownloaded()
    {
        return $this->downloaded;
    }
}
