<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\UserEmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    
    /**
     * Display a paginated list of users with their roles.
     *
     * @return \Illuminate\View\View The view instance displaying the list of users.
     */
    public function index()
    {
        $users = User::query()
                    ->select(['id', 'first_name', 'last_name', 'email', 'is_root_admin', 'created_at'])
                    ->with('roles:id,name')
                    ->latest()
                    ->paginate(25);
        
        return view('admin::users', compact('users'));
    }

    /**
     * Manually verify a user's email address.
     *
     * @param int $id The ID of the user whose email should be verified.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success message after verification.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found.
     * @throws \Throwable If the verification record is not found or cannot be updated.
     */
    public function verify($id)
    {
        $user = User::findOrFail($id);

        $verification = UserEmailVerification::where('user_id', $user->id)
                ->whereNull('verified_at')
                ->latest()
                ->first();

        $verification->update([
            'verified_at' => now(),
        ]);

        $verification->user->update([
            'email_verified_at' => now(),
        ]);

        return redirect()->back()->with('success', __('admin/users/edit.email_verification_alert_success'));
    }
}
