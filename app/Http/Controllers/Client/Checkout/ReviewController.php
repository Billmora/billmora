<?php

namespace App\Http\Controllers\Client\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PackagePrice;
use App\Models\VariantOption;
use App\Services\Package\PricingService;
use App\Services\Package\OrderValidationService;
use App\Services\Package\Client\OrderService;
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
    public function initiate(Request $request, OrderValidationService $validationService)
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
        $packagePrice = PackagePrice::with('package')->findOrFail($validated['price_id']);
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
            return redirect()
                ->route('client.store')
                ->with('error', $validation['message']);
        }

        Session::put('checkout_data', array_merge($validated, ['currency' => $currencyCode]));
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
    public function review(PricingService $pricingService, OrderValidationService $validationService)
    {
        $checkoutData = Session::get('checkout_data');

        if (!$checkoutData) {
            return redirect()
                ->route('client.store')
                ->with('error', 'No checkout data found. Please select a package first.');
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
                    ->with('error', 'The selected configuration is not available for the current currency. Please select again.');
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

        return view('client::checkout.review', [
            'package' => $packagePrice->package,
            'packagePrice' => $packagePrice,
            'variantSelections' => $variantSelections,
            'variants' => $variants,
            'pricing' => $pricing,
            'currency' => $currentCurrency,
            'appliedCoupon' => Session::get('applied_coupon'),
        ]);
    }

    /**
     * Process the checkout and create order with invoice.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\Package\Client\OrderService $orderService
     * @param \App\Services\Package\OrderValidationService $validationService
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function process(Request $request, OrderService $orderService, OrderValidationService $validationService)
    {
        $checkoutData = Session::get('checkout_data');

        if (!$checkoutData) {
            return redirect()
                ->route('client.store')
                ->with('error', 'Session expired. Please select a package again.');
        }

        $rules = [];
        if (Billmora::getGeneral('ordering_notes')) {
            $rules['notes'] = ['nullable', 'string', 'max:1000'];
        }
        if (Billmora::getGeneral('ordering_tos')) {
            $rules['terms_accepted'] = ['required', 'accepted'];
        }

        $validated = $request->validate($rules);

        if (!Auth::check()) {
            session()->put('intended', route('client.checkout.review'));
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
            $result = $orderService->createFromCheckout(
                Auth::id(),
                $packagePrice->id,
                $variantSelections,
                $this->getCoupon(),
                $validated
            );

            Session::forget(['checkout_data', 'applied_coupon']);

            return $this->handleOrderRedirect($result['order'], $result['invoice']);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
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

    /**
     * Handle redirect after successful order creation based on settings.
     *
     * @param \App\Models\Order $order
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Http\Response
     */
    protected function handleOrderRedirect(Order $order, Invoice $invoice)
    {
        // TODO: Return to response until frontend handling is implemented
        switch (Billmora::getGeneral('ordering_redirect')) {
            case 'complete':
                return response($order);
            case 'invoice':
                return response($invoice);
            case 'payment':
                return response($invoice);
                break;
        }
    }
}