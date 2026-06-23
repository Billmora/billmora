<?php

namespace Plugins\Modules\Affiliate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Plugins\Modules\Affiliate\Models\AffiliateMember;

class MemberController extends Controller
{
    use AuditsSystem;

    /**
     * Display a listing of affiliate members.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $members = AffiliateMember::with('user')
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('referral_code', 'like', "%{$search}%");
            })
            ->withCount('referrals')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('module.affiliate::admin.members', compact('members'));
    }

    /**
     * Suspend an affiliate member.
     */
    public function suspend(AffiliateMember $member)
    {
        $member->update(['status' => 'suspended']);

        $this->recordUpdate('module.affiliate.member.suspended', $member->getOriginal(), $member->getChanges());

        return redirect()->back()->with('success', 'Affiliate member has been suspended.');
    }

    /**
     * Activate an affiliate member.
     */
    public function activate(AffiliateMember $member)
    {
        $member->update(['status' => 'active']);

        $this->recordUpdate('module.affiliate.member.activated', $member->getOriginal(), $member->getChanges());

        return redirect()->back()->with('success', 'Affiliate member has been activated.');
    }
}
