<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
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
        'used_at' => 'datetime',
    ];

    /**
     * The coupon associated with this usage record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * The user who used the coupon.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Register model event listeners.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($couponUsage) {
            $couponUsage->coupon->increment('total_uses');
        });

        static::deleted(function ($couponUsage) {
            $couponUsage->coupon->decrement('total_uses');
        });
    }
}
