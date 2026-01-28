<?php

namespace App\Http\Controllers\Client\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PackagePrice;
use App\Models\VariantOption;
use App\Services\Checkout\PricingService;
use App\Services\Checkout\OrderService;
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiate(Request $request)
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

        if (!$this->isConfigValid($packagePrice, $this->buildVariantSelections($validated), $currencyCode)) {
            return redirect()
                ->route('client.store')
                ->with('error', 'The selected billing cycle is not available for your current currency.');
        }

        Session::put('checkout_data', array_merge($validated, ['currency' => $currencyCode]));
        Session::forget('applied_coupon');

        return redirect()->route('client.checkout.review');
    }

    /**
     * Display checkout review page with pricing breakdown.
     *
     * @param \App\Services\Checkout\PricingService $pricingService
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function review(PricingService $pricingService)
    {
        $checkoutData = Session::get('checkout_data');
        if (!$checkoutData) {
            return redirect()
                ->route('client.store')
                ->with('error', 'No checkout data found. Please select a package first.');
        }

        $currentCurrency = Session::get('currency');
        if ($checkoutData['currency'] !== $currentCurrency && 
            !$this->isConfigValidForCurrency($checkoutData, $currentCurrency)) {
            Session::forget(['checkout_data', 'applied_coupon']);
            return redirect()
                ->route('client.store')
                ->with('error', 'The selected configuration is not available for the current currency. Please select again.');
        }

        $checkoutData['currency'] = $currentCurrency;
        Session::put('checkout_data', $checkoutData);

        $packagePrice = PackagePrice::with('package.catalog')->findOrFail($checkoutData['price_id']);
        $variantSelections = $this->buildVariantSelections($checkoutData);
        $variants = $this->loadVariantsDetails($variantSelections);

        return view('client::checkout.review', [
            'package' => $packagePrice->package,
            'packagePrice' => $packagePrice,
            'variantSelections' => $variantSelections,
            'variants' => $variants,
            'pricing' => $pricingService->calculate($packagePrice, $variantSelections, $this->getCoupon()),
            'currency' => $currentCurrency,
            'appliedCoupon' => Session::get('applied_coupon'),
        ]);
    }

    /**
     * Process the checkout and create order with invoice.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\Checkout\PricingService $pricingService
     * @param \App\Services\Checkout\OrderService $orderService
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function process(Request $request, PricingService $pricingService, OrderService $orderService)
    {
        $checkoutData = Session::get('checkout_data');
        // dd($checkoutData);
        if (!$checkoutData) {
            return redirect()
                ->route('client.store')
                ->with('error', 'Session expired. Please select a package again.');
        }

        $rules = [];
        if (Billmora::getGeneral('ordering_notes')) $rules['notes'] = ['nullable', 'string', 'max:1000'];
        if (Billmora::getGeneral('ordering_tos')) $rules['terms_accepted'] = ['required', 'accepted'];

        $validated = $request->validate($rules);

        if (!Auth::check()) {
            session()->put('intended', route('client.checkout.review'));
            return redirect()->route('client.login');
        }

        $currencyCode = Session::get('currency');
        $packagePrice = PackagePrice::with('package')->findOrFail($checkoutData['price_id']);
        $variantSelections = $this->buildVariantSelections($checkoutData);

        if (!$this->isConfigValid($packagePrice, $variantSelections, $currencyCode)) {
            Session::forget(['checkout_data', 'applied_coupon']);
            return redirect()
                ->route('client.store')
                ->with('error', 'Currency mismatch detected. Please select the package again.');
        }

        try {
            $result = $orderService->process(
                Auth::id(),
                $packagePrice,
                $variantSelections,
                $pricingService->calculate($packagePrice, $variantSelections, $this->getCoupon()),
                $this->getCoupon(),
                $validated
            );

            Session::forget(['checkout_data', 'applied_coupon']);
            return $this->handleOrderRedirect($result['order'], $result['invoice']);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    /**
     * Validate package configuration for currency availability.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @param array $variantSelections
     * @param string $currencyCode
     * @return bool
     */
    private function isConfigValid(PackagePrice $packagePrice, array $variantSelections, string $currencyCode): bool
    {
        if (strtolower($packagePrice->type) !== 'free' && !$this->isPriceAvailable($packagePrice, $currencyCode)) {
            return false;
        }

        if (strtolower($packagePrice->type) === 'free' || empty($variantSelections)) {
            return true;
        }

        if (!$packagePrice->package->variants()->whereHas('options.prices', fn($q) => $q->where('name', $packagePrice->name))->exists()) {
            return true;
        }

        return $this->hasValidVariantPrices($variantSelections, $currencyCode, $packagePrice->name);
    }

    /**
     * Validate currency change for existing checkout data.
     *
     * @param array $checkoutData
     * @param string $currencyCode
     * @return bool
     */
    private function isConfigValidForCurrency(array $checkoutData, string $currencyCode): bool
    {
        $packagePrice = PackagePrice::with('package')->findOrFail($checkoutData['price_id']);
        return $this->isConfigValid($packagePrice, $this->buildVariantSelections($checkoutData), $currencyCode);
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
     * Build variant selections array from checkout data.
     *
     * @param array $data
     * @return array
     */
    private function buildVariantSelections(array $data): array
    {
        $selections = [];
        foreach ($data['variants'] ?? [] as $variantId => $optionId) {
            if ($optionId) $selections[(int)$variantId] = [(int)$optionId];
        }
        foreach ($data['variants_multi'] ?? [] as $variantId => $optionIds) {
            $ids = array_filter(array_map('intval', (array)$optionIds));
            if ($ids) $selections[(int)$variantId] = array_values($ids);
        }
        return $selections;
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
     * Check if package price rate is available for currency.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @param string $currencyCode
     * @return bool
     */
    private function isPriceAvailable(PackagePrice $packagePrice, string $currencyCode): bool
    {
        $rate = ($packagePrice->rates[$currencyCode] ?? null);
        return $rate && ($rate['enabled'] ?? false) && ($rate['price'] ?? null) !== null;
    }

    /**
     * Check if all selected variant options have valid prices.
     *
     * @param array $variantSelections
     * @param string $currencyCode
     * @param string $cycleName
     * @return bool
     */
    private function hasValidVariantPrices(array $variantSelections, string $currencyCode, string $cycleName): bool
    {
        $options = VariantOption::with('prices')->whereIn('id', collect($variantSelections)->flatten())->get();
        
        foreach ($variantSelections as $optionIds) {
            foreach ($optionIds as $optionId) {
                $option = $options->find($optionId);
                if (!$option || !$this->hasMatchingPrice($option->prices, $cycleName, $currencyCode)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check if variant price matches cycle and currency requirements.
     *
     * @param mixed $prices
     * @param string $cycleName
     * @param string $currencyCode
     * @return bool
     */
    private function hasMatchingPrice($prices, string $cycleName, string $currencyCode): bool
    {
        foreach ($prices as $price) {
            if ($price->name !== $cycleName) continue;
            if (strtolower($price->type) === 'free') return true;
            $rate = $price->rates[$currencyCode] ?? null;
            if ($rate && ($rate['enabled'] ?? false) && ($rate['price'] ?? null) !== null) return true;
        }
        return false;
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