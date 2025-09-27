<?php

namespace App\Http\Controllers\Admin\Settings\General;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class TermController extends Controller
{
    use AuditsSystem;
        
    /**
     * Applies permission-based middleware for accessing general term settings
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.general.view')->only('index');
        $this->middleware('permission:settings.general.update')->only('update');
    }

    /**
     * Display the term settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.general.term');
    }

    /**
     * Update general term settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing term settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'term_tos' => ['nullable', 'boolean'],
            'term_tos_url' => ['nullable', 'url'],
            'term_tos_content' => ['nullable'],
            'term_toc' => ['nullable', 'boolean'],
            'term_toc_url' => ['nullable', 'url'],
            'term_toc_content' => ['nullable'],
            'term_privacy' => ['nullable', 'boolean'],
            'term_privacy_url' => ['nullable', 'url'],
            'term_privacy_content' => ['nullable'],
        ]);

        $this->updateSettings('general', $validated);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/general.title')]));
    }
}
