<?php

namespace Plugins\Modules\Affiliate\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AffiliateMember extends Model
{
    protected $table = 'pm_affiliate_members';

    protected $fillable = [
        'user_id',
        'referral_code',
        'balance',
        'total_earned',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'balance'      => 'decimal:2',
        'total_earned' => 'decimal:2',
        'joined_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrals()
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    /**
     * Generate a unique referral code.
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }
}
