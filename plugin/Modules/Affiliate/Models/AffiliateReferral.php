<?php

namespace Plugins\Modules\Affiliate\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AffiliateReferral extends Model
{
    protected $table = 'pm_affiliate_referrals';

    protected $fillable = [
        'affiliate_member_id',
        'referred_user_id',
        'converted',
        'converted_at',
    ];

    protected $casts = [
        'converted'    => 'boolean',
        'converted_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(AffiliateMember::class, 'affiliate_member_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class, 'referral_id');
    }
}
