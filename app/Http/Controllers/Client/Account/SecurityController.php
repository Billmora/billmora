<?php

namespace App\Http\Controllers\Client\Account;

use App\Facades\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Traits\AuditsUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    use AuditsUser;

    /**
     * Display the authenticated user's account security page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $currentSessionId = session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $agent = $session->user_agent ?? '';

                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'browser' => $this->parseBrowser($agent),
                    'platform' => $this->parsePlatform($agent),
                    'is_mobile' => $this->isMobile($agent),
                    'is_current' => $session->id === $currentSessionId,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity),
                ];
            });

        return view('client::account.security', compact('user', 'sessions'));
    }

    /**
     * Revoke a specific session.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id The session ID to revoke.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revokeSession(Request $request, string $id)
    {
        $deleted = DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        if (!$deleted) {
            return back()->with('error', __('client/account.sessions.revoke_failed'));
        }

        if ($id === session()->getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('client.login')->with('success', __('client/account.sessions.revoke_success'));
        }

        return back()->with('success', __('client/account.sessions.revoke_success'));
    }

    /**
     * Revoke all other sessions except the current one.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revokeOtherSessions(Request $request)
    {
        if (!$request->confirm_password || !Hash::check($request->confirm_password, Auth::user()->password)) {
            return back()->with('error', __('auth.password.current_mismatch'));
        }

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', session()->getId())
            ->delete();

        return back()->with('success', __('client/account.sessions.revoke_others_success'));
    }

    /**
     * Update the authenticated user's email address.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance containing 'new_email' and 'confirm_password'.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'new_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(Auth::id())],
            'confirm_password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['confirm_password'], $user->password)) {
            return back()->withErrors(['confirm_password' => __('auth.password.current_mismatch')])->withInput();
        }

        $oldUser = $user->only(['email']);

        $user->email = $validated['new_email'];
        $user->save();

        $this->recordUpdate('account.email.updated', $oldUser, $user->fresh()->only(['email']), $request);

        return redirect()->back()->with('success', __('common.update_success', ['attribute' => __('common.email')]));
    }

    /**
     * Update the authenticated user's password.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance containing 'current_password', 'new_password', and 'new_password_confirmation'.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => __('auth.password.current_mismatch')])->withInput();
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        $this->recordUpdate('account.password.updated', [], [], $request);

        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget('2fa_passed');

        return redirect()->route('client.login')->with('success', __('common.update_success', ['attribute' => __('common.password')]));
    }

    /**
     * Parse the browser name from a user agent string.
     */
    private function parseBrowser(string $agent): string
    {
        $browsers = [
            'Edge'    => '/Edg[e\/]?/i',
            'Opera'   => '/OPR|Opera/i',
            'Brave'   => '/Brave/i',
            'Vivaldi' => '/Vivaldi/i',
            'Chrome'  => '/Chrome/i',
            'Firefox' => '/Firefox/i',
            'Safari'  => '/Safari/i',
            'IE'      => '/MSIE|Trident/i',
        ];

        foreach ($browsers as $name => $pattern) {
            if (preg_match($pattern, $agent)) {
                return $name;
            }
        }

        return 'Unknown';
    }

    /**
     * Parse the platform name from a user agent string.
     */
    private function parsePlatform(string $agent): string
    {
        $platforms = [
            'Windows'    => '/Windows/i',
            'macOS'      => '/Macintosh|Mac OS/i',
            'Linux'      => '/Linux/i',
            'Android'    => '/Android/i',
            'iOS'        => '/iPhone|iPad|iPod/i',
            'Chrome OS'  => '/CrOS/i',
        ];

        foreach ($platforms as $name => $pattern) {
            if (preg_match($pattern, $agent)) {
                return $name;
            }
        }

        return 'Unknown';
    }

    /**
     * Determine if the user agent is a mobile device.
     */
    private function isMobile(string $agent): bool
    {
        return (bool) preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $agent);
    }
}
