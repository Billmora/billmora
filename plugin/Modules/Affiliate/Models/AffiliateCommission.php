<?php

namespace Plugins\Modules\Affiliate\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    protected $table = 'pm_affiliate_commissions';

    protected $fillable = [
        'affiliate_member_id',
        'referral_id',
        'invoice_id',
        'amount',
        'currency',
        'status',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(AffiliateMember::class, 'affiliate_member_id');
    }

    public function referral()
    {
        return $this->belongsTo(AffiliateReferral::class, 'referral_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
