<?php

namespace App\Http\Controllers\Admin\Settings\General;

use Billmora;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TermController extends Controller
{

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
     * Store general term settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing term settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
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

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('admin/common.save_success', ['item' => __('admin/settings/general.title')]));
    }
}
