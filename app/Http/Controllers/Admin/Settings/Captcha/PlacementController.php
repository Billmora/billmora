<?php

namespace App\Http\Controllers\Admin\Settings\Captcha;

use App\Http\Controllers\Controller;
use Billmora;
use Illuminate\Http\Request;

class PlacementController extends Controller
{

    /**
     * Display the captcha placement settings page.
     *
     * @return \Illuminate\View\View The view instance for captcha placement settings.
     */
    public function index()
    {
        return view('admin::settings.captcha.placement');
    }

    /**
     * Update captcha placement settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing captcha placement settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If the request validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'placements_enabled_forms' => ['nullable', 'array'],
            'placements_enabled_forms.*' => ['in:login_form,register_form,ticket_form,'],
        ]);

        $validated += [
            'placements_enabled_forms' => [],
        ];

        Billmora::setCaptcha($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/captcha.title')]));
    }
}
