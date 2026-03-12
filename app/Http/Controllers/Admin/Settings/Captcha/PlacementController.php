<?php

namespace App\Http\Controllers\Admin\Settings\Captcha;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlacementController extends Controller
{
    use AuditsSystem;

    /**
     * List of form identifiers where CAPTCHA placement can be configured.
     *
     * @var array<int, string>
     */
    protected $FORM = [
        'login_form',
        'register_form',
        'forgot_password_form',
        'ticket_form',
        'checkout_form',
    ];

    /**
     * Applies permission-based middleware for accessing captcha placement settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.captcha.view')->only(['index']);
        $this->middleware('permission:settings.captcha.update')->only(['update']);
    }

    /**
     * Display the captcha placement settings page.
     *
     * @return \Illuminate\View\View The view instance for captcha placement settings.
     */
    public function index()
    {
        $formOptions = collect($this->FORM)
            ->map(fn ($form) => [
                'value' => $form,
                'title' => ucwords(str_replace('_', ' ', $form)),
            ])
            ->values()
            ->toArray();


        return view('admin::settings.captcha.placement', compact('formOptions'));
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
            'placements_enabled_forms.*' => [Rule::in($this->FORM)],
        ]);

        $validated += [
            'placements_enabled_forms' => [],
        ];

        $this->updateSettings('captcha', $validated);

        Billmora::setCaptcha($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/captcha.title')]));
    }
}
