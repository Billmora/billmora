<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    use AuditsSystem;

    /**
     * Exit the current impersonation session and restore the original admin account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function exit(Request $request)
    {
        $impersonating = session('impersonating');

        if (!$impersonating || empty($impersonating['admin_id'])) {
            return redirect()->route('client.dashboard')->with('error', __('admin/users.impersonate_exit_error'));
        }

        $adminId = $impersonating['admin_id'];
        $userId  = Auth::id();

        $admin = User::find($adminId);

        if (!$admin) {
            session()->forget('impersonating');
            return redirect()->route('client.login')->with('error', __('admin/users.impersonate_exit_error'));
        }

        Auth::login($admin, false);

        session()->forget('impersonating');

        $this->recordCreate('user.impersonate.exit', [
            'admin_id'   => $adminId,
            'admin_email' => $admin->email,
            'user_id'    => $userId,
        ]);

        return redirect()
            ->route('admin.users.summary', ['user' => $userId])
            ->with('success', __('admin/users.impersonate_exit_success'));
    }
}
