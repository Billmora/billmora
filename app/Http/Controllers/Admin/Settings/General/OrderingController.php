<?php

namespace App\Http\Controllers\Admin\Settings\General;

use Billmora;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderingController extends Controller
{
    
    /**
     * Display the ordering settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.general.ordering');
    }

    /**
     * Store general ordering settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing ordering settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
    {
        $validated = $request->validate( [
            'ordering_redirect' => ['required', 'string'],
            'ordering_grace' => ['required', 'integer', 'min_digits:0'],
            'ordering_tos' => ['required', 'boolean'],
            'ordering_notes' => ['required', 'boolean'],
        ]);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('admin/common.save_success', ['item' => __('admin/settings/general.title')]));
    }
}
