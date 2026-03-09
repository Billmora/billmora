<?php

namespace App\Http\Controllers\Admin\Settings\General;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing general misc settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.general.view')->only('index');
        $this->middleware('permission:settings.general.update')->only('update');
    }

    /**
     * Display the misc settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.general.misc');
    }

    /**
     * Update general misc settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing misc settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'misc_debug' => ['boolean'],
            'misc_admin_pagination' => ['required', 'integer', 'min:1'],
            'misc_client_pagination' => ['required', 'integer', 'min:1'],
        ]);

        $this->updateSettings('general', $validated);

        Billmora::setEnv([
            'APP_DEBUG' => $validated['misc_debug'],
        ]);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/general.title')]));
    }
}
