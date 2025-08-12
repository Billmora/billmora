<?php

namespace App\Http\Controllers\Admin\Settings\General;

use Billmora;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{

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
     * Store general affiliate settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing affiliate settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'affiliate_use' => ['nullable', 'boolean'],
            'affiliate_min_payment' => ['required', 'integer', 'min:1'],
            'affiliate_reward' => ['required', 'integer', 'min:1', 'max:100'],
            'affiliate_discount' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('admin/common.save_success', ['item' => __('admin/settings/general.title')]));
    }
}
