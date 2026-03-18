<?php

namespace App\Http\Controllers\Client\Checkout;

use App\Contracts\GatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\PackagePrice;
use App\Models\Plugin;
use App\Models\VariantOption;
use App\Services\Checkout\CartService;
use App\Services\Package\PricingService;
use App\Services\Package\OrderValidationService;
use App\Services\PluginManager;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CartController extends Controller
{

    public function __construct(protected CartService $cartService)
    {
        // 
    }

    /**
     * Display the cart page with items, totals, applied coupon, and applicable payment gateways.
     *
     * @param  \App\Services\PluginManager  $pluginManager
     * @return \Illuminate\View\View
     */
    public function index(PluginManager $pluginManager, OrderValidationService $validationService, PricingService $pricingService) 
    {
        $cartItems = $this->cartService->getItems();
        $currencyCode = Session::get('currency');
        
        $removedItems = false;
        $pricesUpdated = false;

        foreach ($cartItems as $key => $item) {
            $packagePrice = PackagePrice::find($item['package_price_id']);
            $isValid = true;

            if (!$packagePrice) {
                $isValid = false;
            } else {
                if (!$validationService->isPriceAvailableForCurrency($packagePrice, $currencyCode)) {
                    $isValid = false;
                } elseif (!empty($item['variant_selections'])) {
                    $variantValidation = $validationService->validateVariantPrices($item['variant_selections'], $currencyCode, $packagePrice->name);
                    if (!$variantValidation['valid']) {
                        $isValid = false;
                    }
                }
            }

            if (!$isValid) {
                $this->cartService->removeItem($key);
                $removedItems = true;
            } else {
                $pricing = $pricingService->calculatePricing(
                    $packagePrice,
                    $item['variant_selections'] ?? [],
                    null,
                    $currencyCode
                );

                if ($item['unit_price'] != $pricing['recurring_total'] || $item['setup_fee'] != $pricing['setup_fee_total']) {
                    $this->cartService->updateItemPrices($key, $pricing['recurring_total'], $pricing['setup_fee_total']);
                    $pricesUpdated = true;
                }
            }
        }

        if ($removedItems) {
            return redirect()->route('client.checkout.cart')
                ->with('error', __('client/checkout.cart.items_removed_currency_mismatch'));
        }

        if ($pricesUpdated) {
            $cartItems = $this->cartService->getItems();
        }

        $country = Auth::check() ? Auth::user()->billing?->country : null;
        $totals = $this->cartService->getTotals($country);
        $appliedCoupon = Session::get('applied_coupon');

        foreach ($cartItems as &$item) {
            $item['variant_details'] = $this->loadVariantsDetails($item['variant_selections'] ?? []);
        }
        unset($item);

        $activeGateways = Plugin::where('type', 'gateway')->where('is_active', true)->get();
        $gateways = collect();

        foreach ($activeGateways as $gatewayRecord) {
            $instance = $pluginManager->bootInstance($gatewayRecord);
            if ($instance instanceof GatewayInterface) {
                if ($instance->isApplicable((float) $totals['total'], $currencyCode)) {
                    $gateways->push($gatewayRecord);
                }
            }
        }

        return view('client::checkout.cart', compact('cartItems', 'totals', 'currencyCode', 'appliedCoupon', 'gateways'));
    }
    
    /**
     * Validate, calculate pricing, and add a package item into the cart session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\Package\OrderValidationService  $validationService
     * @param  \App\Services\Package\PricingService  $pricingService
     * @param  \App\Services\PluginManager  $pluginManager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function add(Request $request, OrderValidationService $validationService, PricingService $pricingService, PluginManager $pluginManager)
    {
        $validated = $request->validate([
            'price_id' => ['required', Rule::exists('package_prices', 'id')],
            'variants' => ['nullable', 'array'],
            'variants.*' => ['nullable', Rule::exists('variant_options', 'id')],
            'variants_multi' => ['nullable', 'array'],
            'variants_multi.*' => ['array'],
            'variants_multi.*.*' => [Rule::exists('variant_options', 'id')],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:' . Billmora::getGeneral('ordering_max_quantity')],
        ]);

        $currencyCode = Session::get('currency');
        $packagePrice = PackagePrice::with('package.plugin')->findOrFail($validated['price_id']);
        
        $variantSelections = $validationService->buildVariantSelections(
            ($validated['variants'] ?? []) + ($validated['variants_multi'] ?? [])
        );

        $validation = $validationService->validateConfiguration(
            $packagePrice->package,
            $packagePrice,
            $variantSelections,
            $currencyCode
        );

        if (!$validation['valid']) {
            return redirect()->back()->with('error', $validation['message']);
        }

        $configuration = [];
        if ($packagePrice->package->plugin) {
            $instance = $pluginManager->bootInstance($packagePrice->package->plugin);
            if ($instance && method_exists($instance, 'getCheckoutSchema')) {
                $schema = $instance->getCheckoutSchema();
                if (!empty($schema)) {
                    $configRules = [];
                    $configAttributes = [];

                    foreach ($schema as $key => $field) {
                        $configRules["configuration.{$key}"] = explode('|', $field['rules'] ?? 'nullable');
                        $configAttributes["configuration.{$key}"] = $field['label'] ?? $key;
                    }

                    $configValidated = $request->validate($configRules, [], $configAttributes);
                    $configuration = $configValidated['configuration'] ?? [];
                }
            }
        }

        $pricing = $pricingService->calculatePricing(
            $packagePrice,
            $variantSelections,
            null,
            $currencyCode
        );

        $quantity = $validated['quantity'] ?? 1;
        
        $this->cartService->addService(
            $packagePrice->package,
            $packagePrice,
            $pricing['recurring_total'],
            $pricing['setup_fee_total'],
            $configuration,
            $variantSelections,
            $quantity
        );

        return redirect()->route('client.checkout.cart')->with('success', __('client/checkout.cart.item_added'));
    }

    /**
     * Validate and update the quantity of a specific cart item by its cart item ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => ['required', 'integer', 'min:1', 'max:' . Billmora::getGeneral('ordering_max_quantity')],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('client.checkout.cart')
                ->with('error', $validator->errors()->first('quantity'));
        }
        
        $success = $this->cartService->updateQuantity($id, $request->quantity);

        if (!$success) {
            return redirect()
                ->route('client.checkout.cart')
                ->with('error', __('client/checkout.cart.item_not_found'));
        }

        return redirect()->route('client.checkout.cart');
    }

    /**
     * Remove a specific item from the cart session.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove($id)
    {
        $this->cartService->removeItem($id);
        return redirect()->route('client.checkout.cart')->with('success', __('client/checkout.cart.item_removed'));
    }

    /**
     * Load and format variant option details for the given variant selections.
     *
     * @param  array  $variantSelections
     * @return array
     */
    private function loadVariantsDetails(array $variantSelections): array
    {
        if (empty($variantSelections)) return [];
        
        $options = VariantOption::with('variant')
            ->whereIn('id', collect($variantSelections)->flatten())
            ->get()
            ->groupBy('variant_id');

        return collect($variantSelections)->map(function ($optionIds, $variantId) use ($options) {
            $variantOptions = $options->get($variantId);
            return $variantOptions?->isNotEmpty() ? [
                'id' => $variantOptions->first()->variant->id,
                'name' => $variantOptions->first()->variant->name,
                'type' => $variantOptions->first()->variant->type,
                'options' => $variantOptions->whereIn('id', $optionIds)->map(fn($opt) => [
                    'id' => $opt->id, 'name' => $opt->name, 'value' => $opt->value
                ])->values()->toArray()
            ] : null;
        })->filter()->values()->toArray();
    }
}