<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\User;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing users management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index']);
        $this->middleware('permission:users.update')->only(['update']);
    }

    /**
     * Display the credit wallet balances for the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function index(User $user)
    {
        $user->load('credits');

        return view('admin::users.credits', compact('user'));
    }

    /**
     * Validate and update the credit balance for the specified user and currency.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Currency  $currency
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(User $user, Currency $currency, Request $request)
    {
        $validated = $request->validate([
            'credit_balance' => ['required', 'numeric', 'min:0'],
        ]);

        $credit = $user->credits()->firstOrCreate(
            ['currency' => $currency->code]
        );

        $oldCredit = $credit->getOriginal();

        $credit->balance = $validated['credit_balance'];
        $credit->save();

        $this->recordUpdate('user.credit.update', $oldCredit, $credit->getChanges());

        return back()->with('success', __('common.update_success', ['attribute' => $currency->code]));
    }
}
