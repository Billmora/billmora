<?php

namespace Plugins\Modules\Affiliate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Plugins\Modules\Affiliate\Models\AffiliateCommission;
use Plugins\Modules\Affiliate\Models\AffiliateMember;
use Plugins\Modules\Affiliate\Models\AffiliateReferral;
use Plugins\Modules\Affiliate\Models\AffiliateWithdrawal;

class AffiliateController extends Controller
{
    /**
     * Display the affiliate overview dashboard.
     */
    public function index()
    {
        $totalMembers     = AffiliateMember::count();
        $activeMembers    = AffiliateMember::where('status', 'active')->count();
        $totalReferrals   = AffiliateReferral::count();
        $convertedReferrals = AffiliateReferral::where('converted', true)->count();
        $totalCommissions = AffiliateCommission::sum('amount');
        $pendingCommissions = AffiliateCommission::pending()->count();
        $pendingWithdrawals = AffiliateWithdrawal::pending()->count();

        return view('module.affiliate::admin.index', compact(
            'totalMembers',
            'activeMembers',
            'totalReferrals',
            'convertedReferrals',
            'totalCommissions',
            'pendingCommissions',
            'pendingWithdrawals'
        ));
    }
}
