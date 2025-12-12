<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing currencies settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.currencies.view')->only(['index']);
        $this->middleware('permission:settings.currencies.create')->only(['create', 'store']);
        $this->middleware('permission:settings.currencies.update')->only(['edit', 'update', 'setDefault']);
        $this->middleware('permission:settings.currencies.delete')->only(['destroy']);
    }

    /**
     * Display a listing of currencies.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $currencies = Currency::select('id', 'code', 'prefix', 'suffix', 'format', 'base_rate', 'is_default', 'created_at')
                ->orderBy('is_default', 'desc')
                ->paginate(25);
        
        return view('admin::settings.currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new currency.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::settings.currencies.create');
    }

    /**
     * Store a newly created currency in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    { 
        $validated = $request->validate([
            'currency_code' => ['required', 'string', 'size:3', 'unique:currencies,code'],
            'currency_prefix' => ['nullable', 'string', 'max:10'],
            'currency_suffix' => ['nullable', 'string', 'max:10'],
            'currency_format' => ['required', Rule::in(['1234.56', '1,234.56', '1.234,56', '1,234'])],
            'currency_base_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $currency = Currency::create([
            'code' => strtoupper($validated['currency_code']),
            'prefix' => $validated['currency_prefix'] ?? null,
            'suffix' => $validated['currency_suffix'] ?? null,
            'format' => $validated['currency_format'],
            'base_rate' => $validated['currency_base_rate'],
            'is_default' => false,
        ]);

        $this->recordCreate('currency.create', $currency->toArray());

        return redirect()
            ->route('admin.settings.currencies')
            ->with('success', __('common.create_success', ['attribute' => $currency->code]));
    }

    /**
     * Show the form for editing the specified currency.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function edit($id)
    {
        $currency = Currency::findOrFail($id);
        
        return view('admin::settings.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'currency_code' => ['required', 'string', 'size:3', Rule::unique('currencies', 'code')->ignore($currency->id)],
            'currency_prefix' => ['nullable', 'string', 'max:10'],
            'currency_suffix' => ['nullable', 'string', 'max:10'],
            'currency_format' => ['required', Rule::in(['1234.56', '1,234.56', '1.234,56', '1,234'])],
            'currency_base_rate' => [Rule::requiredIf(!$currency->is_default), 'numeric', 'min:0'],
        ]);

        if ($currency->is_default) {
            $validated['currency_base_rate'] = 1;
        }

        $oldCurrency = $currency->getOriginal();

        $currency->update([
            'code' => strtoupper($validated['currency_code']),
            'prefix' => $validated['currency_prefix'] ?? null,
            'suffix' => $validated['currency_suffix'] ?? null,
            'format' => $validated['currency_format'],
            'base_rate' => $validated['currency_base_rate'],
        ]);

        $this->recordUpdate('currency.update', $oldCurrency, $currency->getChanges());

        return redirect()
            ->route('admin.settings.currencies')
            ->with('success', __('common.update_success', ['attribute' => $currency->code]));
    }

    /**
     * Remove the specified currency from storage.
     *
     * Prevents deletion if the currency is set as default.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);

        if ($currency->is_default) {
            return redirect()
                ->route('admin.settings.currencies')
                ->with('error', __('common.delete_failed', [
                    'attribute' => $currency->code,
                ]));
        }

        $this->recordDelete('currency.delete', [
            'code' => $currency->code,
        ]);

        $currency->delete();

        return redirect()
            ->route('admin.settings.currencies')
            ->with('success', __('common.delete_success', [
                'attribute' => $currency->code,
            ]));
    }
}
