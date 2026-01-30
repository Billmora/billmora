<?php

namespace App\Http\Controllers\Admin\Settings\General;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use AuditsSystem;
        
    /**
     * Applies permission-based middleware for accessing general invoice settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.general.view')->only('index');
        $this->middleware('permission:settings.general.update')->only('update');
    }

    /**
     * Display the invoice settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.general.invoice');
    }


    /**
     * Update general invoice settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing invoice settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'invoice_pdf' => ['nullable', 'boolean'],
            'invoice_pdf_size' => ['required', 'in:letter,A4'],
            'invoice_number_increment' => ['required', 'integer', 'min:1'],
            'invoice_number_padding' => ['required', 'integer', 'min:0'],
            'invoice_number_format' => [
                'required',
                'string',
                'regex:/^\S+$/',
                'regex:/\{number\}/',
                'regex:/^[^{}]*(\{(number|day|month|year)\}[^{}]*)*$/',
            ],
        ]);

        $this->updateSettings('general', $validated);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/general.title')]));
    }
}
