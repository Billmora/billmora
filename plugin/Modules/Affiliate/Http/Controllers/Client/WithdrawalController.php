<?php

namespace Plugins\Modules\Affiliate\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Plugins\Modules\Affiliate\Models\AffiliateMember;
use Plugins\Modules\Affiliate\Models\AffiliateWithdrawal;

class WithdrawalController extends Controller
{
    /**
     * Submit a withdrawal request.
     */
    public function store(Request $request)
    {
        $member = AffiliateMember::where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$member) {
            return redirect()->back()->with('error', 'You are not an active affiliate member.');
        }

        // Get minimum withdrawal from plugin config
        $plugin = Plugin::where('type', 'module')
            ->where('provider', 'Affiliate')
            ->where('is_active', true)
            ->first();

        $minWithdrawal = (float) data_get($plugin?->config, 'min_withdrawal', 50000);

        $validated = $request->validate([
            'amount'  => ['required', 'numeric', 'min:' . $minWithdrawal],
            'method'  => ['required', 'string', 'max:255'],
            'detail'  => ['nullable', 'string', 'max:2000'],
        ]);

        if ((float) $validated['amount'] > (float) $member->balance) {
            return redirect()->back()->with('error', 'Withdrawal amount exceeds your available balance.');
        }

        // Check for existing pending withdrawals
        if (AffiliateWithdrawal::where('affiliate_member_id', $member->id)->pending()->exists()) {
            return redirect()->back()->with('error', 'You already have a pending withdrawal request.');
        }

        AffiliateWithdrawal::create([
            'affiliate_member_id' => $member->id,
            'amount'              => $validated['amount'],
            'currency'            => \App\Models\Currency::where('is_default', true)->value('code'),
            'method'              => $validated['method'],
            'detail'              => $validated['detail'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Withdrawal request submitted. Please wait for admin approval.');
    }
}
