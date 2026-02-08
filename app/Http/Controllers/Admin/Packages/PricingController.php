<?php

namespace App\Http\Controllers\Admin\Packages;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Currency;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PricingController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing packages pricing.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:packages.update')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of pricing options for a specific package.
     *
     * @param  int  $id  Package ID
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        $package = Package::with('prices')->findOrFail($id);

        return view('admin::packages.pricing.index', compact('package', 'catalogs'));
    }

    /**
     * Show the form for creating a new pricing entry for the given package.
     *
     * @param  int  $id  Package ID
     * @return \Illuminate\View\View
     */
    public function create($id)
    {
        $package = Package::findOrFail($id);
        $currencies = Currency::orderBy('is_default', 'desc')->get();

        return view('admin::packages.pricing.create', compact('package', 'currencies'));
    }

    /**
     * Store a newly created pricing configuration for a package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Package ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $validated = $request->validate([
            'pricing_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('package_prices', 'name')->where('package_id', $package->id)
            ],
            'pricing_type' => ['required', Rule::in(['free', 'onetime', 'recurring'])],
            'pricing_time_interval' => [
                Rule::requiredIf(fn () => $request->pricing_type === 'recurring'),
                'integer',
                'min:1',
                'nullable'
            ],
            'pricing_billing_period' => [
                Rule::requiredIf(fn () => $request->pricing_type === 'recurring'),
                Rule::in(['daily','weekly','monthly','yearly']),
                'nullable',
            ],
            'rates' => [
                Rule::requiredIf(fn () => in_array($request->pricing_type, ['onetime', 'recurring'])),
                'array',
            ],
            'rates.*.currency' => ['required','string', Rule::exists('currencies', 'code')],
            'rates.*.price' => ['nullable', 'numeric', 'min:1'],
            'rates.*.setup_fee' => ['nullable', 'numeric', 'min:0'],
            'rates.*.enabled' => ['boolean'],
        ]);

        $validator = Validator::make([], []);

        $currencies = Currency::whereIn('code', array_keys($validated['rates'] ?? []))
            ->get()
            ->keyBy('code');

        if (!empty($validated['rates'])) {

            foreach ($validated['rates'] as $code => $rate) {
                $currency = $currencies[$code] ?? null;

                if ($currency && $currency->is_default) {
                    $validated['rates'][$code]['enabled'] = true;
                }

                $enabled = $validated['rates'][$code]['enabled'] ?? false;

                if (!$enabled) {
                    continue;
                }

                if ($validated['pricing_type'] === 'free') {
                    continue;
                }

                if (empty($rate['price']) || $rate['price'] <= 0) {
                    $validator->errors()->add(
                        "rates.$code.price",
                        __('validation.required_if', [
                            'attribute' => __('admin/packages.pricing.price_label'),
                            'other' => __('common.enable'),
                            'value' => 'true',
                        ])
                    );
                }

                if (!array_key_exists('setup_fee', $rate)) {
                    $validator->errors()->add(
                        "rates.$code.setup_fee",
                        __('validation.required_if', [
                            'attribute' => __('admin/packages.pricing.setup_fee_label'),
                            'other' => __('common.enable'),
                            'value' => 'true',
                        ])
                    );
                }
            }
        }

        if ($validator->errors()->any()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $rates = [];

        if (!empty($validated['rates'])) {
            foreach ($validated['rates'] as $code => $data) {

                $rates[$code] = [
                    'currency' => $data['currency'],
                    'price' => $data['price'] ?? null,
                    'setup_fee' => $data['setup_fee'] ?? null,
                    'enabled' => (bool) ($data['enabled'] ?? false),
                ];
            }
        }

        $package->prices()->create([
            'name' => $validated['pricing_name'],
            'type' => $validated['pricing_type'],
            'time_interval' => $validated['pricing_time_interval'] ?? null,
            'billing_period' => $validated['pricing_billing_period'] ?? null,
            'rates' => $rates,
        ]);

        $this->recordCreate('package.pricing.create', [
            'package_id' => $package->id,
            'pricing' => $package->prices()->latest()->first()->toArray(),
        ]);

        return redirect()
            ->route('admin.packages.pricing', ['id' => $package->id])
            ->with('success', __('common.create_success', [
                'attribute' => $validated['pricing_name']
            ]));
    }

    /**
     * Show the form for editing an existing pricing entry for the given package.
     *
     * @param  int  $id  Package ID
     * @param  \App\Models\PackagePrice  $pricing
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function edit($id, PackagePrice $pricing)
    {
        $package = Package::findOrFail($id);

        if ($pricing->package_id !== $package->id) {
            abort(404);
        }

        $currencies = Currency::orderBy('is_default', 'desc')->get();

        return view('admin::packages.pricing.edit', compact('package', 'currencies', 'pricing'));
    }

    /**
     * Update an existing pricing configuration for a package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Package ID
     * @param  int  $priceId  Price row ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id, $priceId)
    {
        $package = Package::findOrFail($id);
        $price = $package->prices()->findOrFail($priceId);

        $validated = $request->validate([
            'pricing_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('package_prices', 'name')
                    ->where('package_id', $package->id)
                    ->ignore($price->id),
            ],
            'pricing_type' => ['required', Rule::in(['free', 'onetime', 'recurring'])],
            'pricing_time_interval' => [
                Rule::requiredIf(fn () => $request->pricing_type === 'recurring'),
                'integer',
                'min:1',
                'nullable'
            ],
            'pricing_billing_period' => [
                Rule::requiredIf(fn () => $request->pricing_type === 'recurring'),
                Rule::in(['daily','weekly','monthly','yearly']),
                'nullable',
            ],
            'rates' => [
                Rule::requiredIf(fn () => in_array($request->pricing_type, ['onetime', 'recurring'])),
                'array',
            ],
            'rates.*.currency' => ['string', Rule::exists('currencies', 'code')],
            'rates.*.price' => ['nullable', 'numeric', 'min:1'],
            'rates.*.setup_fee' => ['nullable', 'numeric', 'min:0'],
            'rates.*.enabled' => ['boolean'],
        ]);

        $validator = Validator::make([], []);

        $currencies = Currency::whereIn('code', array_keys($validated['rates'] ?? []))
            ->get()
            ->keyBy('code');

        if (!empty($validated['rates'])) {

            foreach ($validated['rates'] as $code => $rate) {
                $currency = $currencies[$code] ?? null;

                if ($currency && $currency->is_default) {
                    $validated['rates'][$code]['enabled'] = true;
                }

                $enabled = $validated['rates'][$code]['enabled'] ?? false;

                if (!$enabled) {
                    continue;
                }

                if ($validated['pricing_type'] === 'free') {
                    continue;
                }

                if (empty($rate['price']) || $rate['price'] <= 0) {
                    $validator->errors()->add(
                        "rates[$code][price]",
                        __('validation.required_if', [
                            'attribute' => __('admin/packages.pricing.price_label'),
                            'other' => __('common.enable'),
                            'value' => 'true',
                        ])
                    );
                }

                if (!isset($rate['setup_fee'])) {
                    $validator->errors()->add(
                        "rates[$code][setup_fee]",
                        __('validation.required_if', [
                            'attribute' => __('admin/packages.pricing.setup_fee_label'),
                            'other' => __('common.enable'),
                            'value' => 'true',
                        ])
                    );
                }
            }
        }

        if ($validator->errors()->any()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $rates = [];

        if (!empty($validated['rates'])) {
            foreach ($validated['rates'] as $code => $data) {

                $rates[$code] = [
                    'currency' => $data['currency'],
                    'price' => $data['price'] ?? null,
                    'setup_fee' => $data['setup_fee'] ?? null,
                    'enabled' => (bool) ($data['enabled'] ?? false),
                ];
            }
        }

        $oldPrice = $price->getOriginal();

        $price->update([
            'name' => $validated['pricing_name'],
            'type' => $validated['pricing_type'],
            'time_interval' => $validated['pricing_time_interval'] ?? null,
            'billing_period' => $validated['pricing_billing_period'] ?? null,
            'rates' => $rates,
        ]);

        $this->recordUpdate('package.pricing.update', $oldPrice, $price->getChanges());

        return redirect()
            ->route('admin.packages.pricing', ['id' => $package->id])
            ->with('success', __('common.update_success', [
                'attribute' => $validated['pricing_name']
            ]));
    }

    /**
     * Remove a pricing entry from storage.
     *
     * @param  int  $id  Package ID
     * @param  \App\Models\PackagePrice  $pricing
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy($id, PackagePrice $pricing)
    {
        $package = Package::findOrFail($id);

        if ($pricing->package_id !== $package->id) {
            abort(404);
        }

        $pricing->delete();

        $this->recordDelete('package.pricing.delete', [
            'package_id' => $package->id,
            'pricing_name' => $pricing->name
        ]);

        return redirect()
            ->route('admin.packages.pricing', ['id' => $package->id])
            ->with('success', __('common.delete_success', [
                'attribute' => $pricing->name
            ]));
    }
}
