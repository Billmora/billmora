<?php

namespace App\Models;

use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_root_admin',
        'status',
        'currency',
        'language',
        'department',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'fullname',
        'avatar',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var list<string>
     */
    protected $casts = [
        'password' => 'hashed',
        'is_root_admin' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Accessor for the user's full name. Combines the first name and last name attributes.
     *
     * @return string
     */
    public function getFullnameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Accessor for the user's avatar URL. Generates a Gravatar URL based on the user's email address.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower($this->email));
    }

    /**
     * Get the billing information associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function billing()
    {
        return $this->hasOne(UserBilling::class);
    }

    /**
     * Get the coupon usages for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Get the two-factor authentication settings associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function twoFactor()
    {
        return $this->hasOne(UserTwoFactor::class);
    }

    /**
     * Get the latest email verification record associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getEmailVerification()
    {
        return $this->hasOne(UserEmailVerification::class)->latestOfMany();
    }

    /**
     * Determine if the user's email address has been verified.
     *
     * @return bool True if the user has a non-null email_verified_at timestamp, false otherwise.
     */
    public function isEmailVerified()
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Determine if the user is a root administrator.
     *
     * @return bool True if the user is marked as root admin, false otherwise.
     */
    public function isRootAdmin(): bool
    {
        return $this->is_root_admin === true;
    }

    /**
     * Determine if the user has administrative privileges.
     *
     * @return bool True if the user is an admin, false otherwise.
     */
    public function isAdmin(): bool
    {
        if ($this->isRootAdmin()) {
            return true;
        }

        return $this->permissions()->exists() || $this->roles()->whereHas('permissions')->exists();
    }

    /**
     * Scope a query to only include admin users based on root status, direct permissions, or role permissions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmins(Builder $query)
    {
        return $query->where(function ($q) {
            $q->where('is_root_admin', true)
                ->orWhereHas('permissions')
                ->orWhereHas('roles', fn($r) => $r->whereHas('permissions'));
        });
    }

    /**
     * Determine if the user is considered a client.
     *
     * @return bool True if the user is a client, false otherwise.
     */
    public function isClient(): bool
    {
        if ($this->isRootAdmin()) {
            return false;
        }

        return !$this->permissions()->exists() && !$this->roles()->whereHas('permissions')->exists();
    }
}