<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Tld;
use App\Models\TldPrice;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TldsController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing TLDs management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:tlds.view')->only(['index']);
        $this->middleware('permission:tlds.create')->only(['create', 'store']);
        $this->middleware('permission:tlds.update')->only(['edit', 'update']);
        $this->middleware('permission:tlds.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of TLDs with search functionality.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Tld::with(['plugin:id,name', 'prices']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tld', 'like', "%{$search}%");
            });
        }

        $tlds = $query->orderByDesc('created_at')->paginate(Billmora::getGeneral('misc_admin_pagination'));

        $tlds->appends(['search' => $search]);

        return view('admin::tlds.index', compact('tlds'));
    }

    /**
     * Show the form for creating a new TLD.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin::tlds.create');
    }

    /**
     * Store a newly created TLD in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tld' => ['required', 'string', 'max:255', 'unique:tlds,tld', 'regex:/^[a-zA-Z0-9]/'],
            'plugin_id' => ['nullable', 'exists:plugins,id'],
            'min_years' => ['required', 'integer', 'min:1', 'max:10'],
            'max_years' => ['required', 'integer', 'min:1', 'max:10', 'gte:min_years'],
            'grace_period_days' => ['required', 'integer', 'min:0'],
            'redemption_period_days' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['visible', 'hidden'])],
            'prices' => ['required', 'array'],
            'prices.*.currency' => ['required', 'string', 'size:3'],
            'prices.*.register_price' => ['required_if:prices.*.enabled,1', 'nullable', 'numeric', 'min:0'],
            'prices.*.transfer_price' => ['required_if:prices.*.enabled,1', 'nullable', 'numeric', 'min:0'],
            'prices.*.renew_price' => ['required_if:prices.*.enabled,1', 'nullable', 'numeric', 'min:0'],
            'prices.*.enabled' => ['nullable'],
        ]);

        $tld = Tld::create([
            'tld' => $validated['tld'],
            'plugin_id' => $validated['plugin_id'] ?? null,
            'min_years' => $validated['min_years'],
            'max_years' => $validated['max_years'],
            'grace_period_days' => $validated['grace_period_days'],
            'redemption_period_days' => $validated['redemption_period_days'],
            'status' => $validated['status'],
        ]);

        foreach ($validated['prices'] as $priceData) {
            if (!empty($priceData['enabled'])) {
                $tld->prices()->create([
                    'currency' => $priceData['currency'],
                    'register_price' => $priceData['register_price'] ?? 0,
                    'transfer_price' => $priceData['transfer_price'] ?? 0,
                    'renew_price' => $priceData['renew_price'] ?? 0,
                ]);
            }
        }

        $this->recordCreate('tld.create', $tld->toArray());

        return redirect()->route('admin.tlds')->with('success', __('common.create_success', ['attribute' => $tld->tld]));
    }

    /**
     * Show the form for editing the specified TLD.
     *
     * @param \App\Models\Tld $tld
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Tld $tld)
    {
        $tld->load('prices');

        return view('admin::tlds.edit', compact('tld'));
    }

    /**
     * Update the specified TLD in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Tld $tld
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Tld $tld)
    {
        $validated = $request->validate([
            'tld' => ['required', 'string', 'max:255', Rule::unique('tlds', 'tld')->ignore($tld->id), 'regex:/^[a-zA-Z0-9]/'],
            'plugin_id' => ['nullable', 'exists:plugins,id'],
            'min_years' => ['required', 'integer', 'min:1', 'max:10'],
            'max_years' => ['required', 'integer', 'min:1', 'max:10', 'gte:min_years'],
            'grace_period_days' => ['required', 'integer', 'min:0'],
            'redemption_period_days' => ['required', 'integer', 'min:0'],

            'status' => ['required', Rule::in(['visible', 'hidden'])],
            'prices' => ['required', 'array'],
            'prices.*.currency' => ['required', 'string', 'size:3'],
            'prices.*.register_price' => ['required_if:prices.*.enabled,1', 'nullable', 'numeric', 'min:0'],
            'prices.*.transfer_price' => ['required_if:prices.*.enabled,1', 'nullable', 'numeric', 'min:0'],
            'prices.*.renew_price' => ['required_if:prices.*.enabled,1', 'nullable', 'numeric', 'min:0'],
            'prices.*.enabled' => ['nullable'],
        ]);

        $oldTld = $tld->getOriginal();

        $tld->update([
            'tld' => $validated['tld'],
            'plugin_id' => $validated['plugin_id'] ?? null,
            'min_years' => $validated['min_years'],
            'max_years' => $validated['max_years'],
            'grace_period_days' => $validated['grace_period_days'],
            'redemption_period_days' => $validated['redemption_period_days'],

            'status' => $validated['status'],
        ]);


        $tld->prices()->delete();
        foreach ($validated['prices'] as $priceData) {
            if (!empty($priceData['enabled'])) {
                $tld->prices()->create([
                    'currency' => $priceData['currency'],
                    'register_price' => $priceData['register_price'] ?? 0,
                    'transfer_price' => $priceData['transfer_price'] ?? 0,
                    'renew_price' => $priceData['renew_price'] ?? 0,
                ]);
            }
        }

        $this->recordUpdate('tld.update', $oldTld, $tld->getChanges());

        return redirect()->route('admin.tlds.edit', $tld)->with('success', __('common.update_success', ['attribute' => $tld->tld]));
    }

    /**
     * Remove the specified TLD from storage.
     *
     * @param \App\Models\Tld $tld
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Tld $tld)
    {
        if ($tld->registrants()->where('status', 'active')->exists()) {
            return redirect()->route('admin.tlds')->with('error', __('admin/tlds.delete.has_registrants'));
        }

        $tld->delete();

        $this->recordDelete('tld.delete', $tld->toArray());

        return redirect()->route('admin.tlds')->with('success', __('common.delete_success', ['attribute' => $tld->tld]));
    }
}
