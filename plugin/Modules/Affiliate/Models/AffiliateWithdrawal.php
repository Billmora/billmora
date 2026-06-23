<?php

namespace Plugins\Modules\Affiliate\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawal extends Model
{
    protected $table = 'pm_affiliate_withdrawals';

    protected $fillable = [
        'affiliate_member_id',
        'amount',
        'currency',
        'method',
        'detail',
        'status',
        'note',
        'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(AffiliateMember::class, 'affiliate_member_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
