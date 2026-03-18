<?php

namespace App\Http\Controllers\Admin\Settings\Auth;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing authentication social settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.auth.view')->only(['index']);
        $this->middleware('permission:settings.auth.update')->only(['update']);
    }

    /**
     * Display the authentication social login settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.auth.social');
    }

    /**
     * Update authentication social login settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing social login settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If the request validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'oauth_google_enabled' => ['nullable', 'boolean'],
            'oauth_google_client_id' => ['nullable', 'string', 'max:255'],
            'oauth_google_client_secret' => ['nullable', 'string', 'max:255'],
            'oauth_discord_enabled' => ['nullable', 'boolean'],
            'oauth_discord_client_id' => ['nullable', 'string', 'max:255'],
            'oauth_discord_client_secret' => ['nullable', 'string', 'max:255'],
            'oauth_github_enabled' => ['nullable', 'boolean'],
            'oauth_github_client_id' => ['nullable', 'string', 'max:255'],
            'oauth_github_client_secret' => ['nullable', 'string', 'max:255'],
        ]);

        $this->updateSettings('auth', $validated);

        Billmora::setAuth($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/auth.social.title')]));
    }
}
