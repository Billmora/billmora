<?php

namespace App\Http\Controllers\Admin\Settings\Captcha;

use App\Http\Controllers\Controller;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProviderController extends Controller
{

    /**
     * Applies permission-based middleware for accessing captcha provider settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.captcha.view')->only(['index']);
        $this->middleware('permission:settings.captcha.update')->only(['update']);
    }

    /**
     * Display the captcha provider settings page.
     *
     * @return \Illuminate\View\View The view instance for captcha provider settings.
     */
    public function index()
    {
        return view('admin::settings.captcha.provider');
    }

    /**
     * Update captcha provider settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing captcha provider settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If the request validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider_type' => [
                'nullable',
                Rule::in(['none', 'turnstile', 'recaptchav2', 'hcaptcha']),
            ],
            'provider_site_key' => [
                Rule::requiredIf($request->input('provider_type') !== 'none'),
                'nullable',
                'string',
                'max:255',
            ],
            'provider_secret_key' => [
                Rule::requiredIf($request->input('provider_type') !== 'none'),
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $validated['provider_type'] = $validated['provider_type'] === 'none' ? null : $validated['provider_type'];

        Billmora::setCaptcha([
            'provider_type' => $validated['provider_type'],
        ]);

        Billmora::setEnv([
            'CAPTCHA_SITE_KEY' => $validated['provider_site_key'],
            'CAPTCHA_SECRET_KEY' => $validated['provider_secret_key'],
        ]);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/captcha.title')]));
    }
}
