<?php

namespace App\Services\Checkout;

use App\Models\PackagePrice;
use App\Models\VariantOption;
use App\Models\Coupon;
use App\Models\Currency;
use Illuminate\Support\Facades\Session;

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
        $currency = Session::get('currency');

        $isFree = $packagePrice->type === 'free';
        $basePrice = $isFree ? 0 : ($packagePrice->rates[$currency]['price'] ?? 0);
        $baseSetup = $isFree ? 0 : ($packagePrice->rates[$currency]['setup_fee'] ?? 0);

        $variantItems = [];
        $variantRecurringSum = 0;
        $setupFeeVariants = [];
        $setupFeeVariantSum = 0;

        $allOptionIds = collect($variantSelections)->flatten()->toArray();
        $options = VariantOption::with('variant', 'prices')
            ->whereIn('id', $allOptionIds)
            ->get()
            ->keyBy('id');

        foreach ($variantSelections as $variantId => $optionIds) {
            foreach ($optionIds as $optionId) {
                $option = $options->get($optionId);
                
                if (!$option) {
                    continue;
                }

                $variantPrice = $option->prices
                    ->where('name', $packagePrice->name)
                    ->first();

                if (!$variantPrice) {
                    continue;
                }

                $variantIsFree = $variantPrice->type === 'free';
                $price = $variantIsFree ? 0 : ($variantPrice->rates[$currency]['price'] ?? 0);
                $setup = $variantIsFree ? 0 : ($variantPrice->rates[$currency]['setup_fee'] ?? 0);

                $variantItems[] = [
                    'description' => $option->name,
                    'price' => $price,
                ];

                $variantRecurringSum += $price;

                if ($setup > 0) {
                    $setupFeeVariants[] = [
                        'description' => $option->name,
                        'amount' => $setup,
                    ];
                    $setupFeeVariantSum += $setup;
                }
            }
        }

        $recurringTotal = $basePrice + $variantRecurringSum;
        $setupFeePackage = $baseSetup;
        $setupFeeTotal = $setupFeePackage + $setupFeeVariantSum;
        $subtotal = $recurringTotal;

        $discount = 0;
        if ($coupon) {
            if ($coupon->type === 'percentage') {
                $discount = ($subtotal * $coupon->value) / 100;
            } else {
                $discount = $this->convertCouponAmount($coupon->value, $currency);
            }

            $discount = min($discount, $subtotal);
        }


        $total = $subtotal - $discount + $setupFeeTotal;

        return [
            'base_price' => $basePrice,
            'variant_items' => $variantItems,
            'variant_total' => $variantRecurringSum,
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
     * Convert coupon amount from default currency to target currency.
     *
     * @param float $amountInDefault
     * @param string $targetCurrency
     * @return float
     */
    protected function convertCouponAmount(float $amountInDefault, string $targetCurrency)
    {
        $defaultCurrency = Currency::where('is_default', true)->first();

        if (!$defaultCurrency || $targetCurrency === $defaultCurrency->code) {
            return $amountInDefault;
        }

        $target = Currency::where('code', $targetCurrency)->first();

        if (!$target || !$target->base_rate) {
            return $amountInDefault;
        }

        return $amountInDefault * $target->base_rate;
    }
}
