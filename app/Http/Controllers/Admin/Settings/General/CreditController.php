<?php

namespace App\Http\Controllers\Admin\Settings\General;

use Billmora;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreditController extends Controller
{

    /**
     * Display the credit settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.general.credit');
    }

    /**
     * Store general credit settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing credit settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'credit_use' => ['nullable', 'boolean'],
            'credit_min_deposit' => ['required', 'integer', 'min:1'],
            'credit_max_deposit' => ['required', 'integer', 'min:1', 'max:1000000'],
            'credit_max' => ['required', 'integer', 'min:1', 'max:10000000'],
        ]);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('admin/common.save_success', ['item' => __('admin/settings/general.title')]));
    }
}
