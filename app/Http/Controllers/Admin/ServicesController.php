<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Currency;
use App\Models\PackagePrice;
use App\Models\Plugin;
use App\Models\Service;
use App\Models\VariantOption;
use App\Services\Package\OrderValidationService;
use App\Services\Package\PricingService;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicesController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing services management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user()->hasPermissionTo('services.view')) {
                return redirect()->route('admin.services.cancellations');
            }

            return $next($request);
        })->only(['index']);

        $this->middleware('permission:services.update')->only(['edit', 'update']);
        $this->middleware('permission:services.delete')->only(['destroy']);
    }

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
            'provisioning:id,name'
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
     * Show the form for editing the specified service with packages, pricing, and plugin schemas.
     *
     * @param int $id
     * @param \App\Services\PricingService $pricingService
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function edit($id, PricingService $pricingService, PluginManager $pluginManager)
    {
        $service = Service::with('user')->findOrFail($id);
        
        $currencies = Currency::orderBy('code')->get();

        $packages = Package::with([
                'catalog:id,name',
                'prices', 
                'plugin',
                'variants' => fn($q) => $q->where('status', 'visible'),
                'variants.options.prices'
            ])
            ->where('status', 'visible')
            ->get();

        $packagesPayload = $pricingService->buildPackagesPayload($packages);

        $schemasPayload = [];
        foreach ($packages as $package) {
            if (!$package->plugin) continue;
            $instance = $pluginManager->bootInstance($package->plugin);
            if ($instance && method_exists($instance, 'getCheckoutSchema')) {
                $schema = $instance->getCheckoutSchema();
                if (!empty($schema)) {
                    $schemasPayload[$package->id] = $schema;
                }
            }
        }

        return view('admin::services.edit', compact(
            'service',
            'currencies',
            'packagesPayload',
            'schemasPayload',
        ));
    }

    /**
     * Update the specified service with validated data, configuration, and optional price recalculation.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @param \App\Services\PricingService $pricingService
     * @param \App\Services\Package\OrderValidationService $validationService
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id, PricingService $pricingService, OrderValidationService $validationService, PluginManager $pluginManager)
    {
        $validated = $request->validate([
            'service_status' => ['required', Rule::in(['pending', 'active', 'suspended', 'terminated', 'cancelled'])],
            'service_currency' => ['required', 'string', 'size:3'],
            'service_recalculate_price' => ['nullable'],
            'service_next_due_date' => ['nullable', 'date'],
            'service_price' => ['nullable', 'numeric', 'min:0'],
            'service_setup_fee' => ['nullable', 'numeric', 'min:0'],
            'package_id' => ['required', Rule::exists('packages', 'id')],
            'package_price_id' => [
                'required', 
                Rule::exists('package_prices', 'id')->where(function ($query) use ($request) {
                    return $query->where('package_id', $request->package_id);
                })
            ],
            'variant_selections' => ['nullable', 'array'],
        ]);

        $service = Service::findOrFail($id);
        $oldService = $service->getOriginal();

        $package = Package::with(['plugin', 'variants.options'])->findOrFail($validated['package_id']);
        $packagePrice = PackagePrice::findOrFail($validated['package_price_id']);

        $pluginId = $service->plugin_id;
        if ($service->package_id != $package->id) {
            $pluginId = $package->plugin_id;
        }

        $variantSelections = $validationService->buildVariantSelections(
            $validated['variant_selections'] ?? []
        );

        $validation = $validationService->validateConfiguration(
            $package,
            $packagePrice,
            $variantSelections,
            $validated['service_currency']
        );

        if (!$validation['valid']) {
            return back()->withInput()->with('error', $validation['message']);
        }

        $configuration = $package->provisioning_config ?? [];
        if (!empty($variantSelections)) {
            $optionIds = collect($variantSelections)->flatten()->filter()->toArray();
            if (!empty($optionIds)) {
                $options = VariantOption::with('variant')->whereIn('id', $optionIds)->get();
                foreach ($options as $option) {
                    $key = $option->variant->code ?? null;
                    $value = $option->value;
                    if (empty($key)) continue;
                    if (is_numeric($value)) $value = $value + 0;
                    elseif (strtolower($value) === 'true')  $value = true;
                    elseif (strtolower($value) === 'false') $value = false;
                    $configuration[$key] = $value;
                }
            }
        }

        if ($package->plugin) {
            $instance = $pluginManager->bootInstance($package->plugin);
            if ($instance && method_exists($instance, 'getCheckoutSchema')) {
                $schema = $instance->getCheckoutSchema();
                if (!empty($schema)) {
                    $configRules = [];
                    $configAttributes = [];
                    foreach ($schema as $key => $field) {
                        $configRules["configuration.{$key}"] = is_array($field['rules'] ?? null)
                            ? $field['rules']
                            : explode('|', $field['rules'] ?? 'nullable');
                        $configAttributes["configuration.{$key}"] = $field['label'] ?? $key;
                    }
                    $configValidated = $request->validate($configRules, [], $configAttributes);
                    $configuration = array_merge($configuration, $configValidated['configuration'] ?? []);
                }
            }
        }

        $price = $validated['service_price'] ?? 0;
        $setupFee = $validated['service_setup_fee'] ?? 0;

        if ($request->boolean('service_recalculate_price')) {
            $pricing = $pricingService->calculatePricing($packagePrice, $variantSelections, null, $validated['service_currency']);
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
            'plugin_id' => $pluginId,
            'configuration' => $configuration,
        ]);

        $this->recordUpdate('service.update', $oldService, $service->getChanges());

        return redirect()->route('admin.services.edit', $id)
            ->with('success', __('common.update_success', ['attribute' => $service->name]));
    }

    /**
     * Remove the specified service from database with status validation.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);

        if ($service->status === 'active') {
            return back()->with('error', __('admin/services.delete.active_services'));
        }

        $service->delete();

        $this->recordDelete('service.delete', $service->toArray());

        return redirect()->route('admin.services')
            ->with('success', __('common.delete_success', ['attribute' => $service->name]));
    }
}
