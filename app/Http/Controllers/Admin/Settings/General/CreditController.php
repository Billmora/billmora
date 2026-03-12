<?php

namespace App\Http\Controllers\Admin\Settings\General;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    use AuditsSystem;
    
    /**
     * Applies permission-based middleware for accessing general credit settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.general.view')->only('index');
        $this->middleware('permission:settings.general.update')->only('update');
    }

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
     * Update general credit settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing credit settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'credit_use' => ['nullable', 'boolean'],
            'credit_min_deposit' => ['required', 'numeric', 'min:1'],
            'credit_max_deposit' => ['required', 'numeric', 'min:1'],
            'credit_max' => ['required', 'numeric', 'min:1',],
        ]);

        $this->updateSettings('general', $validated);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/general.title')]));
    }
}
