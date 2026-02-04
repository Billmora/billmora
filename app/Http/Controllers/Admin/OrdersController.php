<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Services\Package\PricingService;
use App\Services\Package\Admin\OrderService;
use App\Services\Package\OrderValidationService;
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

        $orders = $query->latest()->paginate(25);

        return view('admin::orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order with packages and pricing data.
     *
     * @param \App\Services\Package\PricingService $pricingService
     * @return \Illuminate\View\View
     */
    public function create(PricingService $pricingService)
    {
        $users = User::select('id', 'first_name', 'last_name', 'email')->get();
        $currencies = Currency::select('code')->get();
        $coupons = Coupon::select('id', 'code')->where(function ($q) {
                $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
            })->get();
        
        $packages = Package::select('id', 'name', 'catalog_id')
            ->with([
                'prices',
                'catalog:id,name',
                'variants' => function($query) {
                    $query->where('status', 'visible');
                },
                'variants.options.prices'
            ])
            ->get();

        $packagesPayload = $pricingService->buildPackagesPayload($packages);

        return view('admin::orders.create', compact(
            'users',
            'currencies',
            'coupons',
            'packagesPayload'
        ));
    }

    /**
     * Store a newly created order with validation and pricing calculation.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\Package\Admin\OrderService $orderService
     * @param \App\Services\Package\OrderValidationService $validationService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, OrderService $orderService, OrderValidationService $validationService)
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
            $package = Package::with(['prices', 'variants.options.prices', 'catalog'])
                ->findOrFail($validated['order_package']);
            $packagePrice = $package->prices->firstWhere('id', $validated['order_package_billing']);

            if (!$packagePrice) {
                return back()
                    ->withInput()
                    ->with('error', 'Selected billing cycle does not belong to the selected package.');
            }

            $variantSelections = $validationService->buildVariantSelections(
                $validated['variant_options'] ?? []
            );

            $validation = $validationService->validateConfiguration(
                $package,
                $packagePrice,
                $variantSelections,
                $validated['order_currency']
            );

            if (!$validation['valid']) {
                return back()
                    ->withInput()
                    ->with('error', $validation['message']);
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
                $validated['order_status']
            );

            if ($request->boolean('order_email')) {
                // TODO: Send email notify to user
            }

            $this->recordCreate('order.create', isset($result['order']) ? $result['order']->toArray() : (array) $result);

            return redirect()
                ->route('admin.orders')
                ->with('success', __('common.create_success', ['attribute' => $result['order']->order_number]));

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
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => ['required', Rule::in(['pending', 'processing', 'completed', 'cancelled', 'failed'])],
        ]);

        $oldOrder = $order->getOriginal();

        $updateData = [
            'status' => $validated['order_status'],
        ];
    
        switch ($validated['order_status']) {
            case 'completed':
                $updateData['completed_at'] = $order->completed_at ?? now();
                $updateData['cancelled_at'] = null;
                break;
            case 'cancelled':
                $updateData['cancelled_at'] = $order->cancelled_at ?? now();
                $updateData['completed_at'] = null;
                break;
            default:
                $updateData['cancelled_at'] = null;
                $updateData['completed_at'] = null;
                break;
        }
        
        $order->update($updateData);

        $this->recordUpdate('order.update', $oldOrder, $order->getChanges());
        
        return redirect()
                ->route('admin.orders')
                ->with('success', __('common.update_success', ['attribute' => $order->order_number]));
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
