<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
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
        'value' => 'decimal:2',
        'billing_cycles' => 'array',
        'max_uses' => 'integer',
        'max_uses_per_user' => 'integer',
        'total_uses' => 'integer',
        'start_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Packages that this coupon is applicable to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class);
    }

    /**
     * Coupon usage records.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Determine whether the coupon is currently active based on start and expiration dates.
     *
     * @return bool
     */
    public function isActive()
    {
        $now = now();
        
        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }
        
        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check whether the coupon has reached its maximum total usage.
     *
     * @return bool
     */
    public function hasReachedMaxUses()
    {
        if (is_null($this->max_uses)) {
            return false;
        }
        
        return $this->total_uses >= $this->max_uses;
    }

    /**
     * Determine whether a user can still use this coupon.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function canBeUsedBy(User $user)
    {
        if (is_null($this->max_uses_per_user)) {
            return true;
        }
        
        $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
        return $userUsageCount < $this->max_uses_per_user;
    }

    /**
     * Check whether the coupon is valid for a specific package.
     *
     * @param  int  $packageId
     * @return bool
     */
    public function isValidForPackage($packageId)
    {
        $linkedPackages = $this->packages()->count();
        
        if ($linkedPackages === 0) {
            return true;
        }
        
        return $this->packages()->where('package_id', $packageId)->exists();
    }

    /**
     * Check whether the coupon is valid for a billing cycle.
     *
     * @param  string  $pricingName
     * @return bool
     */
    public function isValidForBillingCycle($pricingName)
    {
        if (is_null($this->billing_cycles) || empty($this->billing_cycles)) {
            return true;
        }
        
        return in_array($pricingName, $this->billing_cycles);
    }

    /**
     * Determine whether the coupon can be applied.
     *
     * @param  \App\Models\User       $user
     * @param  int|null               $packageId
     * @param  string|null            $pricingName
     * @return bool
     */
    public function isValid(User $user, $packageId = null, $pricingName = null)
    {
        if (!$this->isActive()) {
            return false;
        }
        
        if ($this->hasReachedMaxUses()) {
            return false;
        }
        
        if (!$this->canBeUsedBy($user)) {
            return false;
        }
        
        if ($packageId && !$this->isValidForPackage($packageId)) {
            return false;
        }

        if ($pricingName && !$this->isValidForBillingCycle($pricingName)) {
            return false;
        }
        
        return true;
    }
}
