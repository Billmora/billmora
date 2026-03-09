<?php

namespace App\Http\Controllers\Admin\Packages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Packages\PricingRequest;
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
     * @param  \App\Models\Package  $package  Package ID
     * @return \Illuminate\View\View
     */
    public function index(Package $package)
    {
        $package->load('prices');

        return view('admin::packages.pricing.index', compact('package'));
    }

    /**
     * Show the form for creating a new pricing entry for the given package.
     *
     * @param  \App\Models\Package  $package  Package ID
     * @return \Illuminate\View\View
     */
    public function create(Package $package)
    {
        $currencies = Currency::orderBy('is_default', 'desc')->get();

        return view('admin::packages.pricing.create', compact('package', 'currencies'));
    }

    /**
     * Store a newly created pricing configuration for a package.
     *
     * @param \App\Http\Requests\Admin\Packages\PricingRequest $request
     * @param \App\Models\Package $package Package ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PricingRequest $request, Package $package)
    {
        $package = $request->getPackage();

        $package->prices()->create($request->getPricingData());

        $this->recordCreate('package.pricing.create', [
            'package_id' => $package->id,
            'pricing' => $package->prices()->latest()->first()->toArray(),
        ]);

        return redirect()
            ->route('admin.packages.pricing', ['package' => $package->id])
            ->with('success', __('common.create_success', [
                'attribute' => $request->input('pricing_name')
            ]));
    }

    /**
     * Show the form for editing an existing pricing entry for the given package.
     *
     * @param  \App\Models\Package  $package  Package ID
     * @param  \App\Models\PackagePrice  $pricing
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Package $package, PackagePrice $pricing)
    {

        if ($pricing->package_id !== $package->id) {
            abort(404);
        }

        $currencies = Currency::orderBy('is_default', 'desc')->get();

        return view('admin::packages.pricing.edit', compact('package', 'currencies', 'pricing'));
    }

    /**
     * Update an existing pricing configuration for a package.
     *
     * @param \App\Http\Requests\Admin\Packages\PricingRequest $request
     * @param \App\Models\Package $package Package ID
     * @param int $priceId Price row ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PricingRequest $request, Package $package, PackagePrice $pricing)
    {
        $package = $request->getPackage();
        $price = $request->getPricing();

        $oldPrice = $price->getOriginal();

        $price->update($request->getPricingData());

        $this->recordUpdate('package.pricing.update', $oldPrice, $price->getChanges());

        return redirect()
            ->route('admin.packages.pricing', ['package' => $package->id])
            ->with('success', __('common.update_success', [
                'attribute' => $request->input('pricing_name')
            ]));
    }

    /**
     * Remove a pricing entry from storage.
     *
     * @param  \App\Models\Package  $package  Package ID
     * @param  \App\Models\PackagePrice  $pricing
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Package $package, PackagePrice $pricing)
    {

        if ($pricing->package_id !== $package->id) {
            abort(404);
        }

        $pricing->delete();

        $this->recordDelete('package.pricing.delete', [
            'package_id' => $package->id,
            'pricing_name' => $pricing->name
        ]);

        return redirect()
            ->route('admin.packages.pricing', ['package' => $package->id])
            ->with('success', __('common.delete_success', [
                'attribute' => $pricing->name
            ]));
    }
}
