<?php

namespace App\Services\Package;

use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\VariantOption;
use App\Models\Coupon;
use Illuminate\Support\Collection;

class PricingService
{
    /**
     * Calculate comprehensive pricing breakdown for package with variants and coupon.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @param array $variantSelections
     * @param \App\Models\Coupon|null $coupon
     * @param string|null $currencyCode
     * @return array
     */
    public function calculatePricing(
        PackagePrice $packagePrice,
        array $variantSelections = [],
        ?Coupon $coupon = null,
        ?string $currencyCode = null
    ): array {
        $currencyCode = $currencyCode ?? session('currency');
        $cycleName = $packagePrice->name;

        $basePrice = $this->getPrice($packagePrice, $currencyCode);
        $setupFeePackage = $this->getSetupFee($packagePrice, $currencyCode);

        $variantTotal = 0;
        $variantItems = [];
        $setupFeeVariants = [];

        if (!empty($variantSelections)) {
            $allOptionIds = collect($variantSelections)->flatten()->unique()->toArray();
            $options = VariantOption::with(['prices', 'variant'])
                ->whereIn('id', $allOptionIds)
                ->get()
                ->keyBy('id');

            foreach ($variantSelections as $variantId => $optionIds) {
                $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];
                
                foreach ($optionIds as $optionId) {
                    $option = $options->get($optionId);
                    if (!$option) continue;

                    $optionPrice = $this->getVariantOptionPrice($option, $cycleName, $currencyCode);
                    $optionSetupFee = $this->getVariantOptionSetupFee($option, $cycleName, $currencyCode);

                    if ($optionPrice > 0) {
                        $variantTotal += $optionPrice;
                        $variantItems[] = [
                            'variant_id' => $variantId,
                            'option_id' => $optionId,
                            'description' => $option->variant->name . ': ' . $option->name,
                            'price' => $optionPrice,
                        ];
                    }

                    if ($optionSetupFee > 0) {
                        $setupFeeVariants[] = [
                            'variant_id' => $variantId,
                            'option_id' => $optionId,
                            'description' => $option->variant->name . ': ' . $option->name,
                            'amount' => $optionSetupFee,
                        ];
                    }
                }
            }
        }

        $recurringTotal = $basePrice + $variantTotal;
        $setupFeeTotal = $setupFeePackage + array_sum(array_column($setupFeeVariants, 'amount'));
        $subtotal = $recurringTotal + $setupFeeTotal;

        $discount = 0;
        if ($coupon) {
            $discount = $this->calculateDiscount($coupon, $recurringTotal);
        }

        $total = max(0, $subtotal - $discount);

        return [
            'base_price' => $basePrice,
            'variant_total' => $variantTotal,
            'variant_items' => $variantItems,
            'recurring_total' => $recurringTotal,
            'setup_fee_package' => $setupFeePackage,
            'setup_fee_variants' => $setupFeeVariants,
            'setup_fee_total' => $setupFeeTotal,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
        ];
    }

    /**
     * Build complete packages payload with prices and variants for all or single currency.
     *
     * @param \Illuminate\Support\Collection $packages
     * @param string|null $singleCurrency
     * @return array
     */
    public function buildPackagesPayload(Collection $packages, ?string $singleCurrency = null): array
    {
        if ($singleCurrency) {
            return $this->buildPackagesPayloadForCurrency($packages, $singleCurrency);
        }

        return $packages->map(function ($package) {
            $availableCurrencies = $this->getPackageCurrencies($package);
            $currenciesData = [];
            
            foreach ($availableCurrencies as $currencyCode) {
                $prices = $this->getAvailablePackagePrices($package, $currencyCode);
                $variants = $this->getAvailableVariants($package, $currencyCode);

                $currenciesData[$currencyCode] = [
                    'prices' => $prices->map(function($price) use ($currencyCode) {
                        return $this->mapPriceToPayload($price, $currencyCode);
                    })->values()->toArray(),
                    'variants' => $this->buildVariantsPayload($variants)
                ];
            }

            return [
                'id' => $package->id,
                'name' => $package->name,
                'catalog_name' => $package->catalog->name,
                'currencies' => $currenciesData
            ];
        })->values()->toArray();
    }

    /**
     * Build packages payload for a specific currency only.
     *
     * @param \Illuminate\Support\Collection $packages
     * @param string $currencyCode
     * @return array
     */
    private function buildPackagesPayloadForCurrency(Collection $packages, string $currencyCode): array
    {
        return $packages->map(function ($package) use ($currencyCode) {
            $prices = $this->getAvailablePackagePrices($package, $currencyCode);
            $variants = $this->getAvailableVariants($package, $currencyCode);

            return [
                'id' => $package->id,
                'name' => $package->name,
                'catalog_name' => $package->catalog->name,
                'prices' => $prices->map(function($price) use ($currencyCode) {
                    return $this->mapPriceToPayload($price, $currencyCode);
                })->values()->toArray(),
                'variants' => $this->buildVariantsPayload($variants)
            ];
        })->values()->toArray();
    }

    /**
     * Get all available currencies for a package based on enabled rates.
     *
     * @param \App\Models\Package $package
     * @return \Illuminate\Support\Collection
     */
    public function getPackageCurrencies(Package $package): Collection
    {
        $currencies = collect();
        
        foreach ($package->prices as $price) {
            if (strtolower($price->type) === 'free') continue;
            
            $rates = $this->decodeRates($price->rates);
            if ($rates) {
                foreach ($rates as $code => $rate) {
                    if ($this->isRateValid($rate)) {
                        $currencies->push($code);
                    }
                }
            }
        }

        return $currencies->unique()->values();
    }

    /**
     * Get package prices available for specified currency.
     *
     * @param \App\Models\Package $package
     * @param string $currencyCode
     * @return \Illuminate\Support\Collection
     */
    public function getAvailablePackagePrices(Package $package, string $currencyCode): Collection
    {
        return $package->prices->filter(function ($price) use ($currencyCode) {
            if (strtolower($price->type) === 'free') {
                return true;
            }

            $rates = $this->decodeRates($price->rates);
            $rate = $rates[$currencyCode] ?? null;
            
            return $rate && $this->isRateValid($rate);
        })->values();
    }

    /**
     * Get variants with options that have prices available for specified currency.
     *
     * @param \App\Models\Package $package
     * @param string $currencyCode
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableVariants(Package $package, string $currencyCode): Collection
    {
        return $package->variants->map(function ($variant) use ($currencyCode) {
            $variant->options = $variant->options->map(function ($option) use ($currencyCode) {
                $filtered = $option->prices->filter(function ($price) use ($currencyCode) {
                    if (strtolower($price->type) === 'free') {
                        return true;
                    }

                    $rates = $this->decodeRates($price->rates);
                    $rate = $rates[$currencyCode] ?? null;
                    
                    return $rate && $this->isRateValid($rate);
                })->values();

                $option->prices_by_name = $filtered->mapWithKeys(function ($price) use ($currencyCode) {
                    return [
                        $price->name => $this->mapPriceToPayload($price, $currencyCode)
                    ];
                });

                $option->prices = $filtered;
                return $option;
            })->values();

            return $variant;
        })->values();
    }

    /**
     * Map price model to payload array with calculated values for currency.
     *
     * @param \App\Models\PackagePrice|\App\Models\VariantPrice $price
     * @param string $currencyCode
     * @return array
     */
    public function mapPriceToPayload($price, string $currencyCode): array
    {
        $isFree = strtolower($price->type) === 'free';
        $rates = $this->decodeRates($price->rates);
        
        $priceValue = $isFree ? 0 : ($rates[$currencyCode]['price'] ?? 0);
        $setupFee = $isFree ? 0 : ($rates[$currencyCode]['setup_fee'] ?? 0);
        $total = $priceValue + $setupFee;

        return [
            'id' => $price->id,
            'name' => $price->name,
            'type' => $price->type,
            'billing_period' => $price->billing_period,
            'price' => (float) $priceValue,
            'setup_fee' => (float) $setupFee,
            'total' => (float) $total,
        ];
    }

    /**
     * Build variants payload with options and their prices by name.
     *
     * @param \Illuminate\Support\Collection $variants
     * @return array
     */
    public function buildVariantsPayload(Collection $variants): array
    {
        return $variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'type' => $variant->type,
                'code' => $variant->code,
                'options' => $variant->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                        'value' => $option->value,
                        'prices_by_name' => $option->prices_by_name ?? []
                    ];
                })->values()->toArray()
            ];
        })->values()->toArray();
    }

    /**
     * Get the base price for package price in specified currency.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @param string $currencyCode
     * @return float
     */
    private function getPrice(PackagePrice $packagePrice, string $currencyCode): float
    {
        if (strtolower($packagePrice->type) === 'free') return 0.0;
        $rate = $packagePrice->rates[$currencyCode] ?? null;
        return $rate && ($rate['enabled'] ?? false) ? (float) ($rate['price'] ?? 0) : 0.0;
    }

    /**
     * Get the setup fee for package price in specified currency.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @param string $currencyCode
     * @return float
     */
    private function getSetupFee(PackagePrice $packagePrice, string $currencyCode): float
    {
        if (strtolower($packagePrice->type) === 'free') return 0.0;
        $rate = $packagePrice->rates[$currencyCode] ?? null;
        return $rate && ($rate['enabled'] ?? false) ? (float) ($rate['setup_fee'] ?? 0) : 0.0;
    }

    /**
     * Get the price for variant option in specified billing cycle and currency.
     *
     * @param \App\Models\VariantOption $option
     * @param string $cycleName
     * @param string $currencyCode
     * @return float
     */
    private function getVariantOptionPrice($option, string $cycleName, string $currencyCode): float
    {
        foreach ($option->prices as $price) {
            if ($price->name !== $cycleName) continue;
            if (strtolower($price->type) === 'free') return 0.0;
            $rate = $price->rates[$currencyCode] ?? null;
            if ($rate && ($rate['enabled'] ?? false)) {
                return (float) ($rate['price'] ?? 0);
            }
        }
        return 0.0;
    }

    /**
     * Get the setup fee for variant option in specified billing cycle and currency.
     *
     * @param \App\Models\VariantOption $option
     * @param string $cycleName
     * @param string $currencyCode
     * @return float
     */
    private function getVariantOptionSetupFee($option, string $cycleName, string $currencyCode): float
    {
        foreach ($option->prices as $price) {
            if ($price->name !== $cycleName) continue;
            if (strtolower($price->type) === 'free') return 0.0;
            $rate = $price->rates[$currencyCode] ?? null;
            if ($rate && ($rate['enabled'] ?? false)) {
                return (float) ($rate['setup_fee'] ?? 0);
            }
        }
        return 0.0;
    }

    /**
     * Calculate discount amount based on coupon type and subtotal.
     *
     * @param \App\Models\Coupon $coupon
     * @param float $subtotal
     * @return float
     */
    private function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if (strtolower($coupon->type) === 'percentage') {
            return ($subtotal * $coupon->value) / 100;
        }
        return min($coupon->value, $subtotal);
    }

    /**
     * Decode JSON rates if stored as string.
     *
     * @param mixed $rates
     * @return array|null
     */
    private function decodeRates($rates): ?array
    {
        return is_string($rates) ? json_decode($rates, true) : $rates;
    }

    /**
     * Check if rate has valid enabled status and price value.
     *
     * @param array|null $rate
     * @return bool
     */
    private function isRateValid(?array $rate): bool
    {
        return $rate && ($rate['enabled'] ?? false) && ($rate['price'] ?? null) !== null;
    }

}
