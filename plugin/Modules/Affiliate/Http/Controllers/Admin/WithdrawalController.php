<?php

namespace Plugins\Modules\Affiliate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Plugins\Modules\Affiliate\Models\AffiliateWithdrawal;

class WithdrawalController extends Controller
{
    use AuditsSystem;

    /**
     * Display a listing of withdrawal requests.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $withdrawals = AffiliateWithdrawal::with('member.user')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('module.affiliate::admin.withdrawals', compact('withdrawals'));
    }

    /**
     * Approve a withdrawal request and deduct balance.
     */
    public function approve(AffiliateWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Withdrawal is not pending.');
        }

        $member = $withdrawal->member;

        if ((float) $member->balance < (float) $withdrawal->amount) {
            return redirect()->back()->with('error', 'Insufficient affiliate balance.');
        }

        $member->decrement('balance', $withdrawal->amount);

        $withdrawal->update([
            'status'       => 'approved',
            'processed_at' => now(),
        ]);

        $this->recordCreate('module.affiliate.withdrawal.approved', $withdrawal->toArray());

        return redirect()->back()->with('success', 'Withdrawal approved and balance deducted.');
    }

    /**
     * Reject a withdrawal request.
     */
    public function reject(Request $request, AffiliateWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Withdrawal is not pending.');
        }

        $withdrawal->update([
            'status'       => 'rejected',
            'note'         => $request->input('note'),
            'processed_at' => now(),
        ]);

        $this->recordCreate('module.affiliate.withdrawal.rejected', $withdrawal->toArray());

        return redirect()->back()->with('success', 'Withdrawal has been rejected.');
    }
}
