<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * Display a paginated listing of orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $orders = Order::with('user')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->user_id, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($request->search, fn($q, $search) => $q->where('order_number', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return OrderResource::collection($orders);
    }

    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \App\Http\Resources\OrderResource
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items', 'services', 'registrants', 'invoices']);

        return new OrderResource($order);
    }

    /**
     * Store a newly created order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\Package\Admin\OrderService  $orderService
     * @param  \App\Services\Package\OrderValidationService  $validationService
     * @param  \App\Services\PluginManager  $pluginManager
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, \App\Services\Package\Admin\OrderService $orderService, \App\Services\Package\OrderValidationService $validationService, \App\Services\PluginManager $pluginManager)
    {
        $validated = $request->validate([
            'user_id'     => ['required', \Illuminate\Validation\Rule::exists('users', 'id')],
            'currency'    => ['required', \Illuminate\Validation\Rule::exists('currencies', 'code')],
            'coupon'      => ['nullable', \Illuminate\Validation\Rule::exists('coupons', 'code')],
            'status'      => ['required', \Illuminate\Validation\Rule::in(['pending', 'processing', 'completed', 'cancelled', 'failed'])],

            'package_items'                     => ['nullable', 'array'],
            'package_items.*.package_id'        => ['required', \Illuminate\Validation\Rule::exists('packages', 'id')],
            'package_items.*.billing_id'        => ['required', \Illuminate\Validation\Rule::exists('package_prices', 'id')],
            'package_items.*.quantity'          => ['nullable', 'integer', 'min:1'],
            'package_items.*.variant_options'   => ['nullable', 'array'],
            'package_items.*.configuration'     => ['nullable', 'array'],

            'domain_items'              => ['nullable', 'array'],
            'domain_items.*.type'       => ['required', \Illuminate\Validation\Rule::in(['register', 'transfer'])],
            'domain_items.*.domain'     => ['required', 'string', 'max:255'],
            'domain_items.*.tld_id'     => ['required', \Illuminate\Validation\Rule::exists('tlds', 'id')],
            'domain_items.*.years'      => ['required', 'integer', 'min:1'],
            'domain_items.*.epp_code'   => ['nullable', 'string'],
        ]);

        if (empty($validated['package_items']) && empty($validated['domain_items'])) {
            return response()->json(['message' => 'At least one package or domain item is required.'], 422);
        }

        try {
            $coupon = $validated['coupon'] ? \App\Models\Coupon::where('code', $validated['coupon'])->first() : null;

            $packageItems = [];
            foreach ($validated['package_items'] ?? [] as $rawPkg) {
                $package = \App\Models\Package::with(['prices', 'variants.options.prices', 'catalog', 'plugin'])->findOrFail($rawPkg['package_id']);
                $packagePrice = $package->prices->firstWhere('id', $rawPkg['billing_id']);

                if (!$packagePrice) {
                    return response()->json(['message' => 'Invalid billing cycle for package.'], 422);
                }

                $variantSelections = $validationService->buildVariantSelections($rawPkg['variant_options'] ?? []);
                $validation = $validationService->validateConfiguration($package, $packagePrice, $variantSelections, $validated['currency']);
                if (!$validation['valid']) {
                    return response()->json(['message' => $validation['message']], 422);
                }

                $configuration = [];
                if ($package->plugin) {
                    $instance = $pluginManager->bootInstance($package->plugin);
                    if ($instance && method_exists($instance, 'getCheckoutSchema')) {
                        $schema = $instance->getCheckoutSchema($package);
                        $cfgValidation = $validationService->validatePluginConfiguration($schema, $rawPkg['configuration'] ?? []);
                        if (!$cfgValidation['valid']) {
                            return response()->json(['message' => $cfgValidation['message']], 422);
                        }
                        $configuration = $cfgValidation['configuration'];
                    }
                }

                $packageItems[] = [
                    'package'  => $package,
                    'price'    => $packagePrice,
                    'variants' => $variantSelections,
                    'config'   => $configuration,
                    'quantity' => $rawPkg['quantity'] ?? 1,
                ];
            }

            $domainItems = [];
            foreach ($validated['domain_items'] ?? [] as $rawDom) {
                $tld = \App\Models\Tld::findOrFail($rawDom['tld_id']);
                $domainItems[] = [
                    'type'     => $rawDom['type'],
                    'domain'   => $rawDom['domain'],
                    'tld'      => $tld,
                    'years'    => $rawDom['years'],
                    'epp_code' => $rawDom['epp_code'] ?? null,
                ];
            }

            $result = $orderService->createOrder(
                $validated['user_id'],
                $packageItems,
                $domainItems,
                $coupon,
                $validated['currency'],
                $validated['status']
            );

            return (new OrderResource($result['order']->load(['user', 'items', 'services', 'registrants', 'invoices'])))
                ->response()
                ->setStatusCode(201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @param  \App\Services\Package\Admin\OrderService  $orderService
     * @return \App\Http\Resources\OrderResource
     */
    public function update(Request $request, Order $order, \App\Services\Package\Admin\OrderService $orderService)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', \Illuminate\Validation\Rule::in(['pending', 'processing', 'completed', 'cancelled', 'failed'])],
        ]);

        try {
            $updatedOrder = $orderService->updateOrderStatus($order, $validated['status']);
            return new OrderResource($updatedOrder->load(['user', 'items', 'services', 'registrants', 'invoices']));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully.'], 200);
    }
}
