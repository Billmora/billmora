<?php

namespace Plugins\Modules\Affiliate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Plugins\Modules\Affiliate\Models\AffiliateCommission;

class CommissionController extends Controller
{
    use AuditsSystem;

    /**
     * Display a listing of all commissions.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $commissions = AffiliateCommission::with(['member.user', 'referral.referredUser', 'invoice'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('module.affiliate::admin.commissions', compact('commissions'));
    }

    /**
     * Approve a pending commission.
     */
    public function approve(AffiliateCommission $commission)
    {
        if ($commission->status !== 'pending') {
            return redirect()->back()->with('error', 'Commission is not pending.');
        }

        $commission->update(['status' => 'approved']);

        $member = $commission->member;
        $member->increment('balance', $commission->amount);
        $member->increment('total_earned', $commission->amount);

        $this->recordCreate('module.affiliate.commission.approved', $commission->toArray());

        return redirect()->back()->with('success', 'Commission approved and balance updated.');
    }

    /**
     * Reject a pending commission.
     */
    public function reject(AffiliateCommission $commission)
    {
        if ($commission->status !== 'pending') {
            return redirect()->back()->with('error', 'Commission is not pending.');
        }

        $commission->update(['status' => 'rejected']);

        $this->recordCreate('module.affiliate.commission.rejected', $commission->toArray());

        return redirect()->back()->with('success', 'Commission has been rejected.');
    }
}
