<?php

namespace App\Http\Controllers\Admin\Users\Edit;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SummaryController extends Controller
{

    /**
     * Applies permission-based middleware for accessing users management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index']);
        $this->middleware('permission:users.impersonate')->only(['impersonate']);
    }

    /**
     * Display the summary information of a specific user.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * @param int                      $id      The ID of the user to display.
     *
     * @return \Illuminate\View\View The view instance displaying the user summary.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user does not exist.
     */
    public function index(Request $request, $id)
    {
        $user = User::with('billing')->findOrFail($id);
        
        return view('admin::users.edit.summary', compact('user'));
    }

    /**
     * Impersonate a specific user account.
     *
     * @param \Illuminate\Http\Request $request The current HTTP request instance.
     * @param int                      $id      The ID of the user to impersonate.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the client dashboard on success,
     *                                           or back with an error message if impersonation fails.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the target user does not exist.
     */
    public function impersonate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', __('admin/users.login_as_user_error'));
        }

        Auth::logout();
        $request->session()->flush();
        Auth::login($user, true);

        return redirect()->route('client.dashboard')->with('success', __('admin/users.login_as_user_success', ['email' => $user->email]));
    }
}
