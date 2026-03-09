<?php

namespace App\Http\Controllers\Admin\Variants;

use Billmora;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Variants;
use App\Models\Currency;
use App\Models\Variant;
use App\Models\VariantOption;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OptionController extends Controller
{
    use AuditsSystem;

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
     * @param  \App\Models\Variant  $variant  Variant ID
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function index(Variant $variant)
    {


        $options = $variant->options()
            ->select(['id', 'variant_id', 'name', 'value', 'created_at'])
            ->paginate(Billmora::getGeneral('misc_admin_pagination'))
            ->withQueryString();

        return view('admin::variants.option.index', compact('variant', 'options'));
    }

    /**
     * Show the form for creating a new option for the given variant.
     *
     * @param  \App\Models\Variant  $variant  Variant ID
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function create(Variant $variant)
    {

        return view('admin::variants.option.create', compact('variant'));
    }

    /**
     * Store a newly created variant option along with its pricing configurations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant  Variant ID
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function store(Variants\OptionRequest $request, Variant $variant)
    {

        $validated = $request->validated();

        $option = DB::transaction(function () use ($validated, $variant) {
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

            return $option->load('prices');
        });

        $this->recordCreate('variant.option.create', $option->toArray());

        return redirect()
            ->route('admin.variants.options', ['variant' => $variant->id])
            ->with('success', __('common.create_success', [
                'attribute' => $validated['variant_options_name']
            ]));
    }

    /**
     * Show the edit form for a specific variant option.
     *
     * @param  \App\Models\Variant  $variant  Variant ID
     * @param  \App\Models\VariantOption  $option
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function edit(Variant $variant, VariantOption $option)
    {


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
     * @param  \App\Models\Variant  $variant  Variant ID
     * @param  \App\Models\VariantOption  $option
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function update(Variants\OptionRequest $request, Variant $variant, VariantOption $option)
    {

        if ($option->variant_id !== $variant->id) {
            abort(404);
        }

        $validated = $request->validated();

        $oldOption = $option->load('prices')->toArray();

        $changesOption = DB::transaction(function () use ($validated, $option) {
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

            return $option->fresh()->load('prices');
        });

        $this->recordUpdate('variant.option.update', $oldOption, $changesOption->toArray());

        return redirect()
            ->route('admin.variants.options', ['variant' => $variant->id])
            ->with('success', __('common.update_success', [
                'attribute' => $validated['variant_options_name'],
            ]));
    }

    /**
     * Delete a specific variant option.
     *
     * @param  \App\Models\Variant  $variant  Variant ID
     * @param  \App\Models\VariantOption  $option
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Throwable
     */
    public function destroy(Variant $variant, VariantOption $option)
    {


        if ($option->variant_id !== $variant->id) {
            abort(404);
        }

        DB::transaction(function () use ($option) {
            $option->delete();
        });

        $this->recordDelete('variant.option.delete', [
            'id' => $option->id,
            'name' => $option->name,
        ]);

        return redirect()
            ->route('admin.variants.options', ['variant' => $variant->id])
            ->with('success', __('common.delete_success', [
                'attribute' => $option->name,
            ]));
    }
}
