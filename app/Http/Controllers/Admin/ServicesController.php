<?php

namespace App\Http\Controllers\Admin;

use Billmora;
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
            if (!$request->user()->can('services.view')) {
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

        $search = $request->input('search');

        $filters = [
            'status' => $request->input('filter_status'),
            'billing_type' => $request->input('filter_billing_type'),
            'billing_cycle' => $request->input('filter_billing_cycle'),
            'plugin_id' => $request->input('filter_plugin_id'),
            'price_min' => $request->input('filter_price_min'),
            'price_max' => $request->input('filter_price_max'),
            'date_from' => $request->input('filter_date_from'),
            'date_to' => $request->input('filter_date_to'),
            'created_at_from' => $request->input('filter_created_at_from'),
            'created_at_to' => $request->input('filter_created_at_to'),
        ];

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('service_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%")
                              ->orWhere('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');

        $services = $this->filterService($query, $filters)
            ->orderBy($sort, $direction)
            ->paginate(Billmora::getGeneral('misc_admin_pagination'))
            ->appends($request->all());

        $billingCycles = \App\Models\PackagePrice::where('type', 'recurring')
            ->select('name')->distinct()->pluck('name', 'name');
        
        $provisionings = \App\Models\Plugin::where('type', 'provisioning')
            ->pluck('name', 'id');

        return view('admin::services.index', compact('services', 'billingCycles', 'provisionings', 'search', 'filters'));
    }

    /**
     * Show the form for editing the specified service using Hybrid Livewire.
     *
     * @param \App\Models\Service $service
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Service $service)
    {
        $service->load('user');
        
        return view('admin::services.edit', compact('service'));
    }

    /**
     * Update the specified service with validated data, configuration, and optional price recalculation.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Service $service
     * @param \App\Services\Package\PricingService $pricingService
     * @param \App\Services\Package\OrderValidationService $validationService
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Service $service, PricingService $pricingService, OrderValidationService $validationService, PluginManager $pluginManager)
    {
        $validated = $request->validate([
            'service_subscription' => ['nullable', 'string'],
            'service_status' => ['required', Rule::in(['pending', 'active', 'suspended', 'terminated', 'cancelled'])],
            'service_currency' => ['required', 'string', 'size:3'],
            'service_recalculate_price' => ['nullable', 'boolean'],
            'service_next_due_date' => ['required', 'date'],
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
            'service_admin_notes' => ['nullable', 'string'],
        ]);


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

        $configuration = array_merge(
            $service->configuration ?? [],
            $package->provisioning_config ?? []
        );

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

        $fields = $service->fields ?? [];
        if ($package->fields->isNotEmpty()) {
            $fieldRules = [];
            $fieldAttributes = [];

            foreach ($package->fields as $field) {
                $rules = [];
                if ($field->required) {
                    $rules[] = 'required';
                } else {
                    $rules[] = 'nullable';
                }

                if (in_array($field->type, ['text', 'textarea', 'password'])) {
                    $rules[] = 'string';
                } elseif ($field->type === 'email') {
                    $rules[] = 'email';
                } elseif ($field->type === 'url') {
                    $rules[] = 'url';
                } elseif ($field->type === 'number') {
                    $rules[] = 'numeric';
                } elseif ($field->type === 'toggle') {
                    $rules[] = 'boolean';
                } elseif (in_array($field->type, ['select', 'radio'])) {
                    $rules[] = Rule::in(array_keys($field->options ?? []));
                }

                $fieldRules["fields.{$field->name}"] = $rules;
                $fieldAttributes["fields.{$field->name}"] = $field->label;
            }

            $fieldsValidated = $request->validate($fieldRules, [], $fieldAttributes);
            $fields = $fieldsValidated['fields'] ?? [];
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
            'subscription_id' => $validated['service_subscription'],
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
            'fields' => $fields,
            'admin_notes' => $validated['service_admin_notes'] ?? null,
        ]);

        $this->recordUpdate('service.update', $oldService, $service->getChanges());

        return redirect()->route('admin.services.edit', $service->id)
            ->with('success', __('common.update_success', ['attribute' => $service->service_number]));
    }

    /**
     * Remove the specified service from database with status validation.
     *
     * @param \App\Models\Service $service
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function destroy(Service $service)
    {


        if ($service->status === 'active') {
            return back()->with('error', __('admin/services.delete.active_services'));
        }

        $service->delete();

        $this->recordDelete('service.delete', $service->toArray());

        return redirect()->route('admin.services')
            ->with('success', __('common.delete_success', ['attribute' => $service->service_number]));
    }

    /**
     * Apply advanced filters to the service query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filterService(\Illuminate\Database\Eloquent\Builder $query, array $filters)
    {
        $query->when($filters['status'], function ($q, $status) {
            $q->where('status', $status);
        });

        $query->when($filters['billing_type'], function ($q, $type) {
            $q->where('billing_type', $type);
        });

        $query->when($filters['billing_cycle'], function ($q, $cycle) {
            $q->whereHas('packagePrice', function ($q2) use ($cycle) {
                $q2->where('name', $cycle);
            });
        });

        $query->when($filters['date_from'], function ($q, $dateFrom) {
            $q->whereDate('next_due_date', '>=', $dateFrom);
        });

        $query->when($filters['date_to'], function ($q, $dateTo) {
            $q->whereDate('next_due_date', '<=', $dateTo);
        });

        $query->when($filters['created_at_from'], function ($q, $dateFrom) {
            $q->whereDate('created_at', '>=', $dateFrom);
        });

        $query->when($filters['created_at_to'], function ($q, $dateTo) {
            $q->whereDate('created_at', '<=', $dateTo);
        });

        $query->when($filters['price_min'], function ($q, $min) {
            $q->where('price', '>=', $min);
        });

        $query->when($filters['price_max'], function ($q, $max) {
            $q->where('price', '<=', $max);
        });

        $query->when($filters['plugin_id'], function ($q, $pluginId) {
            $q->where('plugin_id', $pluginId);
        });

        return $query;
    }
}
