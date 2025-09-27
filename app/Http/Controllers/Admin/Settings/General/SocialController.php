<?php

namespace App\Http\Controllers\Admin\Settings\General;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    use AuditsSystem;
        
    /**
     * Applies permission-based middleware for accessing general social settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.general.view')->only('index');
        $this->middleware('permission:settings.general.update')->only('update');
    }

    /**
     * Display the social settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.general.social');
    }

    /**
     * Update general social settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing social settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'social_discord' => ['nullable', 'url'],
            'social_youtube' => ['nullable', 'url'],
            'social_whatsapp' => ['nullable', 'url'],
            'social_instagram' => ['nullable', 'url'],
            'social_facebook' => ['nullable', 'url'],
            'social_linkedin' => ['nullable', 'url'],
            'social_twitter' => ['nullable', 'url'],
            'social_github' => ['nullable', 'url'],
            'social_reddit' => ['nullable', 'url'],
            'social_skype' => ['nullable', 'url'],
            'social_telegram' => ['nullable', 'url'],
        ]);

        $this->updateSettings('general', $validated);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/general.title')]));
    }
}
