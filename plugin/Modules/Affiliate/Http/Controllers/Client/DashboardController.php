<?php

namespace Plugins\Modules\Affiliate\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Plugins\Modules\Affiliate\Models\AffiliateMember;

class DashboardController extends Controller
{
    /**
     * Display the affiliate dashboard for the logged-in user.
     */
    public function index()
    {
        $member = AffiliateMember::where('user_id', Auth::id())->first();

        if (!$member) {
            return view('module.affiliate::client.join');
        }

        $member->loadCount(['referrals', 'commissions', 'withdrawals']);
        $referrals = $member->referrals()->with('referredUser')->latest()->limit(10)->get();
        $commissions = $member->commissions()->with('invoice')->latest()->limit(10)->get();
        $withdrawals = $member->withdrawals()->latest()->limit(10)->get();

        $referralUrl = url('/?ref=' . $member->referral_code);
        $defaultCurrency = \App\Models\Currency::where('is_default', true)->value('code');

        return view('module.affiliate::client.dashboard', compact(
            'member',
            'referrals',
            'commissions',
            'withdrawals',
            'referralUrl',
            'defaultCurrency'
        ));
    }

    /**
     * Join the affiliate program.
     */
    public function join(Request $request)
    {
        $userId = Auth::id();

        if (AffiliateMember::where('user_id', $userId)->exists()) {
            return redirect()->route('client.modules.affiliate.index')->with('error', 'You are already an affiliate member.');
        }

        AffiliateMember::create([
            'user_id'       => $userId,
            'referral_code' => AffiliateMember::generateReferralCode(),
            'joined_at'     => now(),
        ]);

        return redirect()->route('client.modules.affiliate.index')->with('success', 'Welcome to the affiliate program!');
    }
}
