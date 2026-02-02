<?php

namespace App\Services\Package;

use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\VariantOption;

class OrderValidationService
{
    /**
     * Validate complete order configuration including package, price, variants, and currency.
     *
     * @param \App\Models\Package $package
     * @param \App\Models\PackagePrice $packagePrice
     * @param array $variantSelections
     * @param string $currencyCode
     * @return array
     */
    public function validateConfiguration(
        Package $package,
        PackagePrice $packagePrice,
        array $variantSelections,
        string $currencyCode
    ): array {
        if ($packagePrice->package_id !== $package->id) {
            return $this->error('Selected billing cycle does not belong to the selected package.');
        }

        if (!$this->isPriceAvailableForCurrency($packagePrice, $currencyCode)) {
            return $this->error('The selected billing cycle is not available for your selected currency.');
        }

        if (empty($variantSelections)) {
            return $this->success();
        }

        $variantValidation = $this->validateVariantSelections($package, $variantSelections);
        if (!$variantValidation['valid']) {
            return $variantValidation;
        }

        if (strtolower($packagePrice->type) === 'free') {
            return $this->success();
        }

        $hasVariantPrices = $package->variants()
            ->whereHas('options.prices', fn($q) => $q->where('name', $packagePrice->name))
            ->exists();

        if (!$hasVariantPrices) {
            return $this->success();
        }

        return $this->validateVariantPrices($variantSelections, $currencyCode, $packagePrice->name);
    }

    /**
     *
     * @param \App\Models\Package $package
     * @param array $variantSelections
     * @return array
     */
    public function validateVariantSelections(Package $package, array $variantSelections): array
    {
        $packageVariantIds = $package->variants->pluck('id')->toArray();

        foreach (array_keys($variantSelections) as $variantId) {
            if (!in_array($variantId, $packageVariantIds)) {
                return $this->error('One or more selected variants do not belong to this package.');
            }
        }

        $allOptionIds = collect($variantSelections)->flatten()->unique()->toArray();
        
        $validOptions = VariantOption::whereIn('id', $allOptionIds)
            ->whereHas('variant.packages', function($q) use ($package) {
                $q->where('packages.id', $package->id);
            })
            ->pluck('id')
            ->toArray();

        if (count($allOptionIds) !== count($validOptions)) {
            return $this->error('One or more selected options are invalid or do not belong to this package.');
        }

        return $this->success();
    }

    /**
     * Validate that variant options have valid prices for billing cycle and currency.
     *
     * @param array $variantSelections
     * @param string $currencyCode
     * @param string $cycleName
     * @return array
     */
    public function validateVariantPrices(
        array $variantSelections,
        string $currencyCode,
        string $cycleName
    ): array {
        $allOptionIds = collect($variantSelections)->flatten()->unique()->toArray();
        $options = VariantOption::with('prices')->whereIn('id', $allOptionIds)->get();

        foreach ($variantSelections as $optionIds) {
            foreach ($optionIds as $optionId) {
                $option = $options->find($optionId);
                
                if (!$option) {
                    return $this->error('One or more selected options were not found.');
                }

                if (!$this->hasMatchingPrice($option->prices, $cycleName, $currencyCode)) {
                    return $this->error(
                        "The option '{$option->name}' is not available for the selected billing cycle and currency."
                    );
                }
            }
        }

        return $this->success();
    }

    /**
     * Check if package price is available for specified currency.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @param string $currencyCode
     * @return bool
     */
    public function isPriceAvailableForCurrency(PackagePrice $packagePrice, string $currencyCode): bool
    {
        if (strtolower($packagePrice->type) === 'free') {
            return true;
        }

        $rate = $packagePrice->rates[$currencyCode] ?? null;
        return $rate && ($rate['enabled'] ?? false) && ($rate['price'] ?? null) !== null;
    }

    /**
     * Check if variant option has matching price for billing cycle and currency.
     *
     * @param \Illuminate\Database\Eloquent\Collection $prices
     * @param string $cycleName
     * @param string $currencyCode
     * @return bool
     */
    private function hasMatchingPrice($prices, string $cycleName, string $currencyCode): bool
    {
        foreach ($prices as $price) {
            if ($price->name !== $cycleName) {
                continue;
            }

            if (strtolower($price->type) === 'free') {
                return true;
            }

            $rate = $price->rates[$currencyCode] ?? null;
            if ($rate && ($rate['enabled'] ?? false) && ($rate['price'] ?? null) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param array $variantOptions
     * @return array
     */
    public function buildVariantSelections(array $variantOptions): array
    {
        $selections = [];

        foreach ($variantOptions as $variantId => $optionIds) {
            $variantId = (int) $variantId;
            
            $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];
            
            $optionIds = array_values(array_filter(array_map('intval', $optionIds)));
            
            if (!empty($optionIds)) {
                $selections[$variantId] = $optionIds;
            }
        }

        return $selections;
    }

    /**
     * Return success validation result.
     *
     * @return array
     */
    private function success(): array
    {
        return [
            'valid' => true,
            'message' => null
        ];
    }

    /**
     * Return error validation result with message.
     *
     * @param string $message
     * @return array
     */
    private function error(string $message): array
    {
        return [
            'valid' => false,
            'message' => $message
        ];
    }
}
