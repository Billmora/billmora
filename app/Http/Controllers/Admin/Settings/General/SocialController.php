<?php

namespace App\Http\Controllers\Admin\Settings\General;

use Billmora;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SocialController extends Controller
{

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
     * Store general social settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing social settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
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

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('admin/common.save_success', ['item' => __('admin/settings/general.title')]));
    }
}
