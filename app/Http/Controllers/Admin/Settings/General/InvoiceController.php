<?php

namespace App\Http\Controllers\Admin\Settings\General;

use Billmora;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{

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
     * Store general invoice settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing company settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_pdf' => ['nullable', 'boolean'],
            'invoice_pdf_size' => ['required', 'in:letter,A4'],
            'invoice_pdf_font' => ['required', 'string'],
            'invoice_mass_payment' => ['nullable', 'boolean'],
            'invoice_choose_payment' => ['nullable', 'boolean'],
            'invoice_cancelation_handling' => ['nullable', 'boolean'],
        ]);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('admin/common.save_success', ['item' => __('admin/settings/general.title')]));
    }
}
