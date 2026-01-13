<?php

namespace App\Http\Controllers\Admin\Variants;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Variant;
use App\Models\VariantOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OptionController extends Controller
{

    /**
     * Applies permission-based middleware for accessing variants pricing.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:variants.update')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of options for the given variant.
     *
     * @param  int  $id  Variant ID
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function index($id)
    {
        $variant = Variant::query()
            ->select(['id', 'name'])
            ->findOrFail($id);

        $options = $variant->options()
            ->select(['id', 'variant_id', 'name', 'value', 'created_at'])
            ->paginate(25)
            ->withQueryString();

        return view('admin::variants.option.index', compact('variant', 'options'));
    }

    /**
     * Show the form for creating a new option for the given variant.
     *
     * @param  int  $id  Variant ID
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function create($id)
    {
        $variant = Variant::findOrFail($id);

        return view('admin::variants.option.create', compact('variant'));
    }

    /**
     * Store a newly created variant option along with its pricing configurations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Variant ID
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function store(Request $request, $id)
    {
        $variant = Variant::findOrFail($id);

        $validated = $request->validate([
            'variant_options_name'  => ['required', 'string', 'max:255'],
            'variant_options_value' => ['required', 'string', 'max:255'],
            'pricings' => ['required', 'array', 'min:1'],
            'pricings.*.name' => ['required', 'string', 'max:255', 'distinct'],
            'pricings.*.type' => ['required', Rule::in(['free', 'onetime', 'recurring'])],
            'pricings.*.time_interval' => ['nullable', 'integer', 'min:1'],
            'pricings.*.billing_period' => ['nullable', Rule::in(['hourly', 'daily', 'weekly', 'monthly', 'yearly'])],
            'pricings.*.rates' => ['nullable', 'array'],
            'pricings.*.rates.*.currency' => ['required', 'string', Rule::exists('currencies', 'code')],
            'pricings.*.rates.*.price' => ['nullable', 'numeric', 'min:0'],
            'pricings.*.rates.*.setup_fee' => ['nullable', 'numeric', 'min:0'],
            'pricings.*.rates.*.enabled' => ['boolean'],
        ]);

        $validator  = Validator::make([], []);

        $currencyCodes = collect($validated['pricings'])
            ->flatMap(fn ($p) => array_keys($p['rates'] ?? []))
            ->unique()
            ->values()
            ->all();

        $currencies = Currency::whereIn('code', $currencyCodes)
            ->get()
            ->keyBy('code');

        foreach ($validated['pricings'] as $pIndex => $pricing) {
            $type = $pricing['type'] ?? 'free';

            if ($type === 'recurring') {
                if (empty($pricing['time_interval']) || (int) $pricing['time_interval'] < 1) {
                    $validator->errors()->add(
                        "pricings.$pIndex.time_interval",
                        __('validation.required', [
                            'attribute' => __('admin/variants.options.pricing.time_interval_label'),
                        ])
                    );
                }

                if (empty($pricing['billing_period'])) {
                    $validator->errors()->add(
                        "pricings.$pIndex.billing_period",
                        __('validation.required', [
                            'attribute' => __('admin/variants.options.pricing.billing_period_label'),
                        ])
                    );
                }
            }

            if (in_array($type, ['onetime', 'recurring'], true) && empty($pricing['rates'])) {
                $validator->errors()->add(
                    "pricings.$pIndex.rates",
                    __('validation.required', [
                        'attribute' => __('admin/variants.options.pricing_label'),
                    ])
                );

                continue;
            }

            foreach (($pricing['rates'] ?? []) as $code => $rate) {
                $currency = $currencies[$code] ?? null;

                if ($currency && $currency->is_default) {
                    $validated['pricings'][$pIndex]['rates'][$code]['enabled'] = true;
                }

                $enabled = (bool) ($validated['pricings'][$pIndex]['rates'][$code]['enabled'] ?? false);

                if (!$enabled || $type === 'free') {
                    continue;
                }

                if (empty($rate['price']) || $rate['price'] <= 0) {
                    $validator->errors()->add(
                        "pricings.$pIndex.rates.$code.price",
                        __('validation.required_if', [
                            'attribute' => __('admin/variants.options.pricing.price_label'),
                            'other' => __('common.enable'),
                            'value' => 'true',
                        ])
                    );
                }

                if (!array_key_exists('setup_fee', $rate)) {
                    $validator->errors()->add(
                        "pricings.$pIndex.rates.$code.setup_fee",
                        __('validation.required_if', [
                            'attribute' => __('admin/variants.options.pricing.setup_fee_label'),
                            'other' => __('common.enable'),
                            'value' => 'true',
                        ])
                    );
                }
            }
        }

        if ($validator->errors()->any()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($validated, $variant) {
            $option = $variant->options()->create([
                'name' => $validated['variant_options_name'],
                'value' => $validated['variant_options_value'],
            ]);

            foreach ($validated['pricings'] as $pricing) {
                $rates = [];

                foreach (($pricing['rates'] ?? []) as $code => $rate) {
                    $rates[$code] = [
                        'currency' => $rate['currency'],
                        'price' => $rate['price'] ?? null,
                        'setup_fee' => $rate['setup_fee'] ?? null,
                        'enabled' => (bool) ($rate['enabled'] ?? false),
                    ];
                }

                $option->prices()->create([
                    'name' => $pricing['name'],
                    'type' => $pricing['type'],
                    'time_interval' => $pricing['time_interval'] ?? null,
                    'billing_period' => $pricing['billing_period'] ?? null,
                    'rates' => $rates,
                ]);
            }
        });

        return redirect()
            ->route('admin.variants.options', ['id' => $variant->id])
            ->with('success', __('common.create_success', [
                'attribute' => $validated['variant_options_name']
            ]));
    }

    /**
     * Show the edit form for a specific variant option.
     *
     * @param  int  $id  Variant ID
     * @param  \App\Models\VariantOption  $option
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function edit($id, VariantOption $option)
    {
        $variant = Variant::query()
            ->select(['id', 'name'])
            ->findOrFail($id);

        if ($option->variant_id !== $variant->id) {
            abort(404);
        }

        $option->load(['prices' => function ($q) {
            $q->select([
                'id',
                'variant_option_id',
                'name',
                'type',
                'time_interval',
                'billing_period',
                'rates',
                'created_at',
            ])->orderBy('id');
        }]);

        $currencies = Currency::query()
            ->select(['id', 'code', 'is_default'])
            ->orderByDesc('is_default')
            ->orderBy('code')
            ->get();

        $pricingsFromDb = $option->prices->map(function ($price) {
            return [
                'name' => $price->name,
                'type' => $price->type,
                'time_interval' => $price->time_interval,
                'billing_period' => $price->billing_period,
                'rates' => $price->rates ?? [],
            ];
        })->values()->all();

        $pricings = old('pricings', $pricingsFromDb);

        return view('admin::variants.option.edit', compact(
            'variant',
            'option',
            'currencies',
            'pricings',
        ));
    }

    /**
     * Update an existing variant option and its pricing configurations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Variant ID
     * @param  \App\Models\VariantOption  $option
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function update(Request $request, $id, VariantOption $option)
    {
        $variant = Variant::findOrFail($id);

        if ($option->variant_id !== $variant->id) {
            abort(404);
        }

        $validated = $request->validate([
            'variant_options_name'  => ['required', 'string', 'max:255'],
            'variant_options_value' => ['required', 'string', 'max:255'],
            'pricings' => ['required', 'array', 'min:1'],
            'pricings.*.name' => ['required', 'string', 'max:255', 'distinct'],
            'pricings.*.type' => ['required', Rule::in(['free', 'onetime', 'recurring'])],
            'pricings.*.time_interval' => ['nullable', 'integer', 'min:1'],
            'pricings.*.billing_period' => ['nullable', Rule::in(['hourly', 'daily', 'weekly', 'monthly', 'yearly'])],
            'pricings.*.rates' => ['nullable', 'array'],
            'pricings.*.rates.*.currency' => ['required', 'string', Rule::exists('currencies', 'code')],
            'pricings.*.rates.*.price' => ['nullable', 'numeric', 'min:0'],
            'pricings.*.rates.*.setup_fee' => ['nullable', 'numeric', 'min:0'],
            'pricings.*.rates.*.enabled' => ['boolean'],
        ]);

        $validator = Validator::make([], []);

        $currencyCodes = collect($validated['pricings'])
            ->flatMap(fn ($p) => array_keys($p['rates'] ?? []))
            ->unique()
            ->values()
            ->all();

        $currencies = Currency::whereIn('code', $currencyCodes)
            ->get()
            ->keyBy('code');

        foreach ($validated['pricings'] as $pIndex => $pricing) {
            $type = $pricing['type'] ?? 'free';

            if ($type === 'recurring') {
                if (empty($pricing['time_interval']) || (int) $pricing['time_interval'] < 1) {
                    $validator->errors()->add(
                        "pricings.$pIndex.time_interval",
                        __('validation.required', [
                            'attribute' => __('admin/variants.options.pricing.time_interval_label'),
                        ])
                    );
                }

                if (empty($pricing['billing_period'])) {
                    $validator->errors()->add(
                        "pricings.$pIndex.billing_period",
                        __('validation.required', [
                            'attribute' => __('admin/variants.options.pricing.billing_period_label'),
                        ])
                    );
                }
            }

            if (in_array($type, ['onetime', 'recurring'], true) && empty($pricing['rates'])) {
                $validator->errors()->add(
                    "pricings.$pIndex.rates",
                    __('validation.required', [
                        'attribute' => __('admin/variants.options.pricing_label'),
                    ])
                );
                continue;
            }

            foreach (($pricing['rates'] ?? []) as $code => $rate) {
                $currency = $currencies[$code] ?? null;

                if ($currency && $currency->is_default) {
                    $validated['pricings'][$pIndex]['rates'][$code]['enabled'] = true;
                }

                $enabled = (bool) ($validated['pricings'][$pIndex]['rates'][$code]['enabled'] ?? false);

                if (!$enabled || $type === 'free') {
                    continue;
                }

                if (empty($rate['price']) || $rate['price'] <= 0) {
                    $validator->errors()->add(
                        "pricings.$pIndex.rates.$code.price",
                        __('validation.required_if', [
                            'attribute' => __('admin/variants.options.pricing.price_label'),
                            'other' => __('common.enable'),
                            'value' => 'true',
                        ])
                    );
                }

                if (!array_key_exists('setup_fee', $rate)) {
                    $validator->errors()->add(
                        "pricings.$pIndex.rates.$code.setup_fee",
                        __('validation.required_if', [
                            'attribute' => __('admin/variants.options.pricing.setup_fee_label'),
                            'other' => __('common.enable'),
                            'value' => 'true',
                        ])
                    );
                }
            }
        }

        if ($validator->errors()->any()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($validated, $option) {
            $option->update([
                'name' => $validated['variant_options_name'],
                'value' => $validated['variant_options_value'],
            ]);

            $option->prices()->delete();

            foreach ($validated['pricings'] as $pricing) {
                $rates = [];

                foreach (($pricing['rates'] ?? []) as $code => $rate) {
                    $rates[$code] = [
                        'currency' => $rate['currency'],
                        'price' => $rate['price'] ?? null,
                        'setup_fee' => $rate['setup_fee'] ?? null,
                        'enabled' => (bool) ($rate['enabled'] ?? false),
                    ];
                }

                $option->prices()->create([
                    'name' => $pricing['name'],
                    'type' => $pricing['type'],
                    'time_interval' => $pricing['time_interval'] ?? null,
                    'billing_period' => $pricing['billing_period'] ?? null,
                    'rates' => $rates,
                ]);
            }
        });

        return redirect()
            ->route('admin.variants.options', ['id' => $variant->id])
            ->with('success', __('common.update_success', [
                'attribute' => $validated['variant_options_name'],
            ]));
    }

    /**
     * Delete a specific variant option.
     *
     * @param  int  $id  Variant ID
     * @param  \App\Models\VariantOption  $option
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Throwable
     */
    public function destroy($id, VariantOption $option)
    {
        $variant = Variant::query()
            ->select(['id'])
            ->findOrFail($id);

        if ($option->variant_id !== $variant->id) {
            abort(404);
        }

        DB::transaction(function () use ($option) {
            $option->delete();
        });

        return redirect()
            ->route('admin.variants.options', ['id' => $variant->id])
            ->with('success', __('common.delete_success', [
                'attribute' => $option->name,
            ]));
    }
}
