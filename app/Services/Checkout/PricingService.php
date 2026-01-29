<?php

namespace App\Services\Checkout;

use App\Models\PackagePrice;
use App\Models\Coupon;
use App\Models\VariantOption;

class PricingService
{
    /**
     * Calculate pricing breakdown for a package with variants and optional coupon.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @param array $variantSelections
     * @param \App\Models\Coupon|null $coupon
     * @return array
     */
    public function calculate(PackagePrice $packagePrice, array $variantSelections, ?Coupon $coupon = null)
    {
        $cycleName = $packagePrice->name;
        $currencyCode = session('currency');

        $basePrice = $this->getPrice($packagePrice, $currencyCode);
        $setupFeePackage = $this->getSetupFee($packagePrice, $currencyCode);

        $variantTotal = 0;
        $variantItems = [];
        $setupFeeVariants = [];

        if (!empty($variantSelections)) {
            $allOptionIds = collect($variantSelections)->flatten()->unique()->toArray();
            $options = VariantOption::with(['prices', 'variant'])->whereIn('id', $allOptionIds)->get()->keyBy('id');

            foreach ($variantSelections as $variantId => $optionIds) {
                foreach ($optionIds as $optionId) {
                    $option = $options->get($optionId);
                    if (!$option) {
                        continue;
                    }

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
     * Get the base price for the package price in specified currency.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @param string $currencyCode
     * @return float
     */
    private function getPrice(PackagePrice $packagePrice, string $currencyCode): float
    {
        if (strtolower($packagePrice->type) === 'free') {
            return 0.0;
        }

        $rate = $packagePrice->rates[$currencyCode] ?? null;
        return $rate && ($rate['enabled'] ?? false) ? (float) ($rate['price'] ?? 0) : 0.0;
    }

    /**
     * Get the setup fee for the package price in specified currency.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @param string $currencyCode
     * @return float
     */
    private function getSetupFee(PackagePrice $packagePrice, string $currencyCode): float
    {
        if (strtolower($packagePrice->type) === 'free') {
            return 0.0;
        }

        $rate = $packagePrice->rates[$currencyCode] ?? null;
        return $rate && ($rate['enabled'] ?? false) ? (float) ($rate['setup_fee'] ?? 0) : 0.0;
    }

    /**
     * Get the price for a variant option in specified billing cycle and currency.
     *
     * @param \App\Models\VariantOption $option
     * @param string $cycleName
     * @param string $currencyCode
     * @return float
     */
    private function getVariantOptionPrice($option, string $cycleName, string $currencyCode): float
    {
        foreach ($option->prices as $price) {
            if ($price->name !== $cycleName) {
                continue;
            }

            if (strtolower($price->type) === 'free') {
                return 0.0;
            }

            $rate = $price->rates[$currencyCode] ?? null;
            if ($rate && ($rate['enabled'] ?? false)) {
                return (float) ($rate['price'] ?? 0);
            }
        }

        return 0.0;
    }

    /**
     * Get the setup fee for a variant option in specified billing cycle and currency.
     *
     * @param \App\Models\VariantOption $option
     * @param string $cycleName
     * @param string $currencyCode
     * @return float
     */
    private function getVariantOptionSetupFee($option, string $cycleName, string $currencyCode): float
    {
        foreach ($option->prices as $price) {
            if ($price->name !== $cycleName) {
                continue;
            }

            if (strtolower($price->type) === 'free') {
                return 0.0;
            }

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
}
