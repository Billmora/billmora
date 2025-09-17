<?php

namespace App\Http\Controllers\Admin\Settings\General;

use App\Http\Controllers\Controller;
use Billmora;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{

    /**
     * Applies permission-based middleware for accessing general affiliate settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.general.view')->only('index');
        $this->middleware('permission:settings.general.update')->only('update');
    }

    /**
     * Display the affiliate settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.general.affiliate');
    }

    /**
     * Update general affiliate settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing affiliate settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'affiliate_use' => ['nullable', 'boolean'],
            'affiliate_min_payment' => ['required', 'integer', 'min:1'],
            'affiliate_reward' => ['required', 'integer', 'min:1', 'max:100'],
            'affiliate_discount' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/general.title')]));
    }
}
