<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Currency;
use App\Models\PackagePrice;
use App\Models\Service;
use App\Services\Package\PricingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicesController extends Controller
{
    /**
     * Display a paginated list of services with search functionality.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Service::with([
            'user:id,email,first_name,last_name',
            'package:id,name,slug,catalog_id', 
            'package.catalog:id,name',
            'packagePrice:id,package_id,name,type,billing_period',
            'provisioning:id,name,driver'
        ]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%")
                              ->orWhere('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        $services = $query->latest()->paginate(25);

        return view('admin::services.index', compact('services'));
    }

    /**
     * Show the form for editing the specified service with pricing calculations.
     *
     * @param int $id
     * @param \App\Services\PricingService $pricingService
     * @return \Illuminate\Contracts\View\View
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function edit($id, PricingService $pricingService)
    {
        $service = Service::with('user')->findOrFail($id);
        
        $currencies = Currency::orderBy('code')->get();

        $rawPackages = Package::with([
            'catalog:id,name',
            'prices', 
            'variants' => function($q) {
                $q->where('status', 'visible');
            }, 
            'variants.options.prices'
        ])
        ->where('status', 'visible')
        ->get();

        $packagesPayload = $pricingService->buildPackagesPayload($rawPackages);

        return view('admin::services.edit', compact(
            'service', 
            'currencies', 
            'packagesPayload'
        ));
    }

    /**
     * Update the specified service with validated data and optional price recalculation.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @param \App\Services\PricingService $pricingService
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id, PricingService $pricingService)
    {
        $validated = $request->validate([
            'service_status' => ['required', Rule::in(['pending', 'active', 'suspended', 'terminated', 'cancelled'])],
            'service_currency' => ['required', 'string', 'size:3'],
            'service_recalculate_price' => ['nullable'],
            'service_next_due_date' => ['nullable', 'date'],
            'service_price' => ['nullable', 'numeric', 'min:0'],
            'service_setup_fee' => ['nullable', 'numeric', 'min:0'],
            'package_id' => ['required', Rule::exists('packages', 'id')],
            'package_price_id' => ['required', Rule::exists('package_prices', 'id')],
            'variant_selections' => ['nullable', 'array'],
        ]);

        $service = Service::findOrFail($id);
        $package = Package::findOrFail($validated['package_id']);
        $packagePrice = PackagePrice::where('package_id', $package->id)
            ->where('id', $validated['package_price_id'])
            ->firstOrFail();

        $variantSelections = [];
        if (!empty($validated['variant_selections'])) {
            foreach ($validated['variant_selections'] as $variantId => $optionVal) {
                if (is_array($optionVal)) {
                    $variantSelections[$variantId] = array_map('intval', $optionVal);
                } else {
                    $variantSelections[$variantId] = (int) $optionVal;
                }
            }
        }

        $price = $validated['service_price'] ?? 0;
        $setupFee = $validated['service_setup_fee'] ?? 0;

        if ($request->boolean('service_recalculate_price')) {
            $pricing = $pricingService->calculatePricing(
                $packagePrice,
                $variantSelections,
                null,
                $validated['service_currency']
            );

            $price = $pricing['recurring_total'];
            $setupFee = $pricing['setup_fee_total'];
        }

        $service->update([
            'package_id' => $package->id,
            'package_price_id' => $packagePrice->id,
            'name' => $package->name, 
            'status' => $validated['service_status'],
            'currency' => $validated['service_currency'],
            'billing_type' => $packagePrice->type,
            'billing_interval' => $packagePrice->time_interval,
            'billing_period' => $packagePrice->billing_period,
            'price' => $price,
            'setup_fee' => $setupFee,
            'variant_selections' => $variantSelections,
            'next_due_date' => $validated['service_next_due_date'],
        ]);

        return redirect()->route('admin.services.edit', $id)
            ->with('success', __('common.update_success', ['attribute' => $service->name]));
    }
}
