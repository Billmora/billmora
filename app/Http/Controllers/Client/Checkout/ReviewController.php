<?php

namespace App\Http\Controllers\Client\Checkout;

use App\Contracts\GatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PackagePrice;
use App\Models\Plugin;
use App\Models\VariantOption;
use App\Services\CaptchaService;
use App\Services\Package\Client\OrderRedirectService;
use App\Services\Package\PricingService;
use App\Services\Package\OrderValidationService;
use App\Services\Package\Client\OrderService;
use App\Services\PluginManager;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /**
     * Initialize checkout process with package and variant selections.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\Package\OrderValidationService $validationService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiate(Request $request, OrderValidationService $validationService, PluginManager $pluginManager)
    {
        $validated = $request->validate([
            'price_id' => ['required', Rule::exists('package_prices', 'id')],
            'variants' => ['nullable', 'array'],
            'variants.*' => ['nullable', Rule::exists('variant_options', 'id')],
            'variants_multi' => ['nullable', 'array'],
            'variants_multi.*' => ['array'],
            'variants_multi.*.*' => [Rule::exists('variant_options', 'id')],
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
            return redirect()->route('client.store')->with('error', $validation['message']);
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

        Session::put('checkout_data', [
            ...$validated,
            'currency' => $currencyCode,
            'configuration' => $configuration,
        ]);
        Session::forget('applied_coupon');

        return redirect()->route('client.checkout.review');
    }

    /**
     * Display checkout review page with pricing breakdown.
     *
     * @param \App\Services\Package\PricingService $pricingService
     * @param \App\Services\Package\OrderValidationService $validationService
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function review(PricingService $pricingService, OrderValidationService $validationService, PluginManager $pluginManager)
    {
        $checkoutData = Session::get('checkout_data');

        if (!$checkoutData) {
            return redirect()
                ->route('client.store')
                ->with('error', __('client/checkout.session.missing_data'));
        }

        $currentCurrency = Session::get('currency');

        if ($checkoutData['currency'] !== $currentCurrency) {
            $packagePrice = PackagePrice::with('package')->findOrFail($checkoutData['price_id']);
            $variantSelections = $validationService->buildVariantSelections(
                ($checkoutData['variants'] ?? []) + ($checkoutData['variants_multi'] ?? [])
            );

            $validation = $validationService->validateConfiguration(
                $packagePrice->package,
                $packagePrice,
                $variantSelections,
                $currentCurrency
            );

            if (!$validation['valid']) {
                Session::forget(['checkout_data', 'applied_coupon']);
                return redirect()
                    ->route('client.store')
                    ->with('error', __('client/checkout.session.currency_mismatch'));
            }

            $checkoutData['currency'] = $currentCurrency;
            Session::put('checkout_data', $checkoutData);
        }

        $packagePrice = PackagePrice::with('package.catalog')->findOrFail($checkoutData['price_id']);
        $variantSelections = $validationService->buildVariantSelections(
            ($checkoutData['variants'] ?? []) + ($checkoutData['variants_multi'] ?? [])
        );
        $variants = $this->loadVariantsDetails($variantSelections);

        $pricing = $pricingService->calculatePricing(
            $packagePrice,
            $variantSelections,
            $this->getCoupon(),
            $currentCurrency
        );

        $activeGateways = Plugin::where('type', 'gateway')->where('is_active', true)->get();
        $gateways = collect();

        foreach ($activeGateways as $gatewayRecord) {
            $instance = $pluginManager->bootInstance($gatewayRecord);
            
            if ($instance instanceof GatewayInterface) {
                if ($instance->isApplicable((float) $pricing['total'], $currentCurrency)) {
                    $gateways->push($gatewayRecord);
                }
            }
        }

        return view('client::checkout.review', [
            'package' => $packagePrice->package,
            'packagePrice' => $packagePrice,
            'variantSelections' => $variantSelections,
            'variants' => $variants,
            'pricing' => $pricing,
            'currency' => $currentCurrency,
            'appliedCoupon' => Session::get('applied_coupon'),
            'gateways' => $gateways,
        ]);
    }

    /**
     * Process the checkout and create order with invoice.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\Package\Client\OrderService $orderService
     * @param \App\Services\Package\OrderValidationService $validationService
     * @param \App\Services\Package\Client\OrderRedirectService $redirectService
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function process(Request $request, OrderService $orderService, OrderValidationService $validationService, OrderRedirectService $redirectService)
    {
        $checkoutData = Session::get('checkout_data');

        if (!$checkoutData) {
            return redirect()
                ->route('client.store')
                ->with('error', __('client/checkout.session.expired'));
        }

        $rules = [
            'payment_method' => [
                'required', 
                Rule::exists('plugins', 'id')
                    ->where('type', 'gateway')
                    ->where('is_active', true)
            ],
        ];
        if (Billmora::getGeneral('ordering_notes')) {
            $rules['notes'] = ['nullable', 'string', 'max:1000'];
        }
        if (Billmora::getGeneral('ordering_tos')) {
            $rules['terms_accepted'] = ['required', 'accepted'];
        }

        $validated = $request->validate($rules);

        CaptchaService::verifyOrFail('checkout_form', $request);

        if (!Auth::check()) {
            session()->put('url.intended', route('client.checkout.review'));
            return redirect()->route('client.login');
        }

        $currencyCode = Session::get('currency');
        $packagePrice = PackagePrice::with('package')->findOrFail($checkoutData['price_id']);
        $variantSelections = $validationService->buildVariantSelections(
            ($checkoutData['variants'] ?? []) + ($checkoutData['variants_multi'] ?? [])
        );

        $validation = $validationService->validateConfiguration(
            $packagePrice->package,
            $packagePrice,
            $variantSelections,
            $currencyCode
        );

        if (!$validation['valid']) {
            Session::forget(['checkout_data', 'applied_coupon']);
            return redirect()
                ->route('client.store')
                ->with('error', $validation['message']);
        }

        try {
            $result = $orderService->createOrder(
                Auth::id(),
                $packagePrice->id,
                $variantSelections,
                $this->getCoupon(),
                $validated,
                $checkoutData['configuration'] ?? []
            );

            Session::forget(['checkout_data', 'applied_coupon']);

            return $redirectService->handle($result['order'], $result['invoice']);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the order completion page and clear completed order session data.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function complete()
    {
        if (!Session::has('completed_order_data')) {
            return redirect()->route('client.store')->with('error', __('client/checkout.session.missing_data'));
        }

        $sessionData = Session::pull('completed_order_data');

        $order = Order::with('package')->findOrfail($sessionData['order_id']);
        $invoice = Invoice::findOrfail($sessionData['invoice_id']);

        return view('client::checkout.complete', compact('order', 'invoice'));
    }

    /**
     * Get applied coupon from session or null.
     *
     * @return \App\Models\Coupon|null
     */
    private function getCoupon(): ?Coupon
    {
        return Session::get('applied_coupon') ? Coupon::find(Session::get('applied_coupon')['id']) : null;
    }

    /**
     * Load variant details for display in review.
     *
     * @param array $variantSelections
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