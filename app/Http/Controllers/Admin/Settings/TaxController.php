<?php

namespace App\Http\Controllers\Admin\Settings;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing taxes settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.taxes.view')->only(['index']);
        $this->middleware('permission:settings.taxes.create')->only(['create', 'store']);
        $this->middleware('permission:settings.taxes.update')->only(['edit', 'update']);
        $this->middleware('permission:settings.taxes.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of taxes ordered alphabetically by name.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $taxes = Tax::orderBy('name', 'asc')
            ->paginate(Billmora::getGeneral('misc_admin_pagination'));
        
        return view('admin::settings.taxes.index', compact('taxes'));
    }

    /**
     * Show the form for creating a new tax.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::settings.taxes.create');
    }

    /**
     * Validate and persist a new tax record, uppercasing the country code when provided, and record the creation to the system log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tax_name' => ['required', 'string', 'max:255', Rule::unique('taxes', 'name')],
            'tax_value' => ['required', 'numeric', 'min:0'],
            'tax_country' => ['nullable', 'string', Rule::in(array_keys(config('utils.countries')))],
        ]);

        $tax = Tax::create([
            'name' => $validated['tax_name'],
            'value' => $validated['tax_value'],
            'country' => $validated['tax_country'] ? strtoupper($validated['tax_country']) : null,
        ]);

        $this->recordCreate('tax.create', $tax->toArray());

        return redirect()
            ->route('admin.settings.taxes')
            ->with('success', __('common.create_success', ['attribute' => $tax->name]));
    }

    /**
     * Show the form for editing an existing tax.
     *
     * @param  \App\Models\Tax  $tax
     * @return \Illuminate\View\View
     */
    public function edit(Tax $tax)
    {
        return view('admin::settings.taxes.edit', compact('tax'));
    }

    /**
     * Validate and apply updates to an existing tax record, uppercasing the country code when provided, and record the before and after state to the system log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tax           $tax
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'tax_name' => ['required', 'string', 'max:255', Rule::unique('taxes', 'name')->ignore($tax->id)],
            'tax_value' => ['required', 'numeric', 'min:0'],
            'tax_country' => ['nullable', 'string', Rule::in(array_keys(config('utils.countries')))],
        ]);

        $oldTax = $tax->getOriginal();

        $tax->update([
            'name' => $validated['tax_name'],
            'value' => $validated['tax_value'],
            'country' => $validated['tax_country'] ? strtoupper($validated['tax_country']) : null,
        ]);

        $this->recordUpdate('tax.update', $oldTax, $tax->getChanges());

        return redirect()
            ->route('admin.settings.taxes')
            ->with('success', __('common.update_success', ['attribute' => $tax->name]));
    }

    /**
     * Delete the given tax record and record the deletion to the system log.
     *
     * @param  \App\Models\Tax  $tax
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Tax $tax)
    {
        $this->recordDelete('tax.delete', $tax->toArray());

        $tax->delete();

        return redirect()
            ->route('admin.settings.taxes')
            ->with('success', __('common.delete_success', ['attribute' => $tax->name]));
    }
}
