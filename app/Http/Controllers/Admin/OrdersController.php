<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Services\Package\PricingService;
use App\Services\Package\Admin\OrderService;
use App\Services\Package\OrderValidationService;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrdersController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing orders management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:orders.view')->only(['index']);
        $this->middleware('permission:orders.create')->only(['create', 'store']);
        $this->middleware('permission:orders.update')->only(['edit', 'update']);
        $this->middleware('permission:orders.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of all orders with search functionality.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Order::with([
            'user:id,email,first_name,last_name', 
            'package:id,name,slug', 
            'packagePrice:id,package_id,name,type,billing_period',
            'coupon:id,code,type,value'
        ]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%");
                });
            });
        }

        $orders = $query->latest()->paginate(Billmora::getGeneral('misc_admin_pagination'));

        return view('admin::orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order with pricing, packages, and plugin schemas.
     *
     * @param \App\Services\PricingService $pricingService
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Contracts\View\View
     */
    public function create(PricingService $pricingService, PluginManager $pluginManager)
    {
        $users = User::select('id', 'first_name', 'last_name', 'email')->get();
        $coupons = Coupon::select('id', 'code')->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->get();

        $packages = Package::select('id', 'name', 'catalog_id', 'plugin_id') 
            ->with([
                'prices',
                'catalog:id,name',
                'plugin',
                'variants' => fn($q) => $q->where('status', 'visible'),
                'variants.options.prices',
            ])
            ->get();

        $packagesPayload = $pricingService->buildPackagesPayload($packages);

        $checkoutSchema = [];
        foreach ($packages as $package) {
            if (!$package->plugin) continue;
            $instance = $pluginManager->bootInstance($package->plugin);
            if ($instance && method_exists($instance, 'getCheckoutSchema')) {
                $schema = $instance->getCheckoutSchema();
                if (!empty($schema)) {
                    $checkoutSchema[$package->id] = $schema;
                }
            }
        }

        return view('admin::orders.create', compact(
            'users',
            'coupons',
            'packagesPayload',
            'checkoutSchema',
        ));
    }

    /**
     * Store a newly created order with validation, pricing, configuration, and auditing.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\Package\Admin\OrderService $orderService
     * @param \App\Services\Package\OrderValidationService $validationService
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, OrderService $orderService, OrderValidationService $validationService, PluginManager $pluginManager)
    {
        $validated = $request->validate([
            'order_user' => ['required', Rule::exists('users', 'email')],
            'order_currency' => ['required', Rule::exists('currencies', 'code')],
            'order_coupon' => ['nullable', Rule::exists('coupons', 'code')],
            'order_status' => ['required', Rule::in(['pending', 'processing', 'completed', 'cancelled', 'failed'])],
            'order_package' => ['required', Rule::exists('packages', 'id')],
            'order_package_billing' => ['required', Rule::exists('package_prices', 'id')],
            'variant_options' => ['nullable', 'array'],
        ]);

        try {
            $user = User::where('email', $validated['order_user'])->firstOrFail();
            $package = Package::with(['prices', 'variants.options.prices', 'catalog', 'plugin'])
                ->findOrFail($validated['order_package']);
            $packagePrice = $package->prices->firstWhere('id', $validated['order_package_billing']);

            if (!$packagePrice) {
                return back()->withInput()->with('error', __('client/store.order.cycle_mismatch'));
            }

            $variantSelections = $validationService->buildVariantSelections(
                $validated['variant_options'] ?? []
            );

            $validation = $validationService->validateConfiguration(
                $package, $packagePrice, $variantSelections, $validated['order_currency']
            );

            if (!$validation['valid']) {
                return back()->withInput()->with('error', $validation['message']);
            }

            $configuration = [];
            if ($package->plugin) {
                $instance = $pluginManager->bootInstance($package->plugin);
                if ($instance && method_exists($instance, 'getCheckoutSchema')) {
                    $schema = $instance->getCheckoutSchema();
                    if (!empty($schema)) {
                        $configRules      = [];
                        $configAttributes = [];
                        foreach ($schema as $key => $field) {
                            $configRules["configuration.{$key}"] = is_array($field['rules'] ?? null)
                                ? $field['rules']
                                : explode('|', $field['rules'] ?? 'nullable');
                            $configAttributes["configuration.{$key}"] = $field['label'] ?? $key;
                        }
                        $configValidated = $request->validate($configRules, [], $configAttributes);
                        $configuration = $configValidated['configuration'] ?? [];
                    }
                }
            }

            $coupon = $validated['order_coupon'] 
                ? Coupon::where('code', $validated['order_coupon'])->first() 
                : null;

            $result = $orderService->createOrder(
                $user->id,
                $package,
                $packagePrice,
                $variantSelections,
                $coupon,
                $validated['order_currency'],
                $validated['order_status'],
                $configuration ,
            );

            if ($request->boolean('order_email')) {
                // TODO: Send email notify to user
            }

            $this->recordCreate('order.create', isset($result['order']) ? $result['order']->toArray() : (array) $result);

            return redirect()
                ->route('admin.orders')
                ->with('success', __('common.create_success', ['attribute' => $result['order']->order_number]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; 
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('common.create_failed', ['attribute' => $e->getMessage()]));
        }
    }

    /**
     * Show the form for editing the specified order with variant details.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\View\View
     */
    public function edit(Order $order)
    {
        $order->load([
            'package.catalog',
            'package.variants.options',
            'packagePrice'
        ]);

        $variantDetails = [];
    
        if ($order->variant_selections && is_array($order->variant_selections)) {
            foreach ($order->variant_selections as $variantId => $optionIds) {
                $variant = $order->package->variants->firstWhere('id', $variantId);
                
                if (!$variant) continue;
                
                $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];
                
                $selectedOptions = $variant->options->whereIn('id', $optionIds);
                
                foreach ($selectedOptions as $option) {
                    $variantDetails[] = [
                        'name' => "{$variant->name}: {$option->name}"
                    ];
                }
            }
        }

        return view('admin::orders.edit', compact('order', 'variantDetails'));
    }

    /**
     * Update the specified order status with timestamp handling.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Order $order, OrderService $orderService)
    {
        $validated = $request->validate([
            'order_status' => ['required', Rule::in(['pending', 'processing', 'completed', 'cancelled', 'failed'])],
        ]);

        $oldOrder = $order->getOriginal();

        try {
            $updatedOrder = $orderService->updateOrderStatus($order, $validated['order_status']);

            $this->recordUpdate('order.update', $oldOrder, $updatedOrder->getChanges());
            
            return redirect()
                    ->route('admin.orders')
                    ->with('success', __('common.update_success', ['attribute' => $order->order_number]));

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('common.update_failed', ['attribute' => $e->getMessage()]));
        }
    }

    /**
     * Remove the specified order from database.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Order $order)
    {
        $tempOrder = $order;

        $order->delete();

        $this->recordDelete('order.delete', [
            'id' => $tempOrder->id,
            'name' => $tempOrder->order_number,
        ]);

        return redirect()->route('admin.orders')->with('success', __('common.delete_success', ['attribute' => $tempOrder->order_number]));
    }
}
