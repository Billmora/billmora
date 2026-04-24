<?php

namespace App\Services\Checkout;

use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Tax;
use App\Models\Tld;
use App\OrderItemType;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'billmora_cart';

    /**
     * Retrieve all current items stored in the cart session.
     *
     * @return array
     */
    public function getItems(): array
    {
        return Session::get($this->sessionKey, []);
    }

    /**
     * Add a service package to the cart session, incrementing quantity if an identical configuration already exists.
     *
     * @param  \App\Models\Package  $package
     * @param  \App\Models\PackagePrice  $price
     * @param  float  $resolvedPrice
     * @param  float  $resolvedSetupFee
     * @param  array  $config
     * @param  array  $variants
     * @param  int  $quantity
     * @return string
     */
    public function addService(Package $package, PackagePrice $price, float $resolvedPrice, float $resolvedSetupFee = 0, array $config = [], array $variants = [], int $quantity = 1): string
    {
        $cartItemId = md5((string) $package->id . '_' . $price->id . '_' . json_encode($config) . '_' . json_encode($variants));

        $cart = $this->getItems();

        if (isset($cart[$cartItemId])) {
            if ($package->allow_quantity === 'multiple') {
                $newQuantity = $cart[$cartItemId]['quantity'] + $quantity;
                Session::put("{$this->sessionKey}.{$cartItemId}.quantity", $newQuantity);
            }

            return $cartItemId;
        }

        if ($package->allow_quantity === 'single') {
            $quantity = 1;
        }

        $itemData = [
            'id' => $cartItemId,
            'type' => OrderItemType::Service->value,
            'package_id' => $package->id,
            'package_price_id' => $price->id,
            'description' => $package->name,
            'cycle_name' => $price->name,
            'billing_type' => $price->type,
            'billing_interval' => $price->time_interval,
            'billing_period' => $price->billing_period,
            'unit_price' => $resolvedPrice,
            'setup_fee' => $resolvedSetupFee,
            'quantity' => $quantity,
            'allow_quantity' => $package->allow_quantity,
            'config_options' => $config,
            'variant_selections' => $variants,
        ];

        Session::put("{$this->sessionKey}.{$cartItemId}", $itemData);

        return $cartItemId;
    }

    /**
     * Add a domain registration or transfer to the cart session.
     *
     * @param  \App\Models\Tld  $tld
     * @param  string  $domainName
     * @param  string  $registrationType  'register' or 'transfer'
     * @param  int  $years
     * @param  float  $resolvedTotalPrice
     * @param  string|null  $eppCode
     * @param  array  $nameservers
     * @return string
     */
    public function addDomain(Tld $tld, string $domainName, string $registrationType, int $years, float $resolvedTotalPrice, ?string $eppCode = null, array $nameservers = []): string
    {
        if ($registrationType === 'register' && !\Billmora::getGeneral('domain_registration_enabled')) {
            throw new \Exception(__('client/store.domain_disabled'));
        }

        if ($registrationType === 'transfer' && !\Billmora::getGeneral('domain_transfer_enabled')) {
            throw new \Exception(__('client/store.domain_disabled'));
        }

        $cartItemId = md5('domain_' . $domainName . '_' . $registrationType);

        $cart = $this->getItems();


        if (isset($cart[$cartItemId])) {
            return $cartItemId;
        }

        $itemData = [
            'id' => $cartItemId,
            'type' => OrderItemType::Domain->value,
            'tld_id' => $tld->id,
            'description' => ucfirst($registrationType) . ' Domain - ' . $domainName,
            'cycle_name' => $years . ' Year' . ($years > 1 ? 's' : ''),
            'billing_type' => 'onetime',
            'billing_interval' => null,
            'billing_period' => null,
            'unit_price' => $resolvedTotalPrice,
            'setup_fee' => 0,
            'quantity' => 1,
            'allow_quantity' => 'single',
            'config_options' => [
                'domain' => $domainName,
                'type' => $registrationType,
                'years' => $years,
                'epp_code' => $eppCode,
                'nameservers' => $nameservers,
            ],
            'variant_selections' => [],
        ];

        Session::put("{$this->sessionKey}.{$cartItemId}", $itemData);

        return $cartItemId;
    }

    /**
     * Update the quantity of a specific cart item by its cart item ID.
     *
     * @param  string  $cartItemId
     * @param  int  $quantity
     * @return bool
     */
    public function updateQuantity(string $cartItemId, int $quantity): bool
    {
        $cart = $this->getItems();

        if (isset($cart[$cartItemId])) {
            if ($cart[$cartItemId]['allow_quantity'] === 'single') {
                Session::put("{$this->sessionKey}.{$cartItemId}.quantity", 1);
            } else {
                Session::put("{$this->sessionKey}.{$cartItemId}.quantity", max(1, $quantity));
            }
            return true;
        }

        return false;
    }

    /**
     * Remove a specific item from the cart session and clear coupon if the cart becomes empty.
     *
     * @param  string  $cartItemId
     * @return void
     */
    public function removeItem(string $cartItemId): void
    {
        Session::forget("{$this->sessionKey}.{$cartItemId}");

        if (empty($this->getItems())) {
            Session::forget('applied_coupon');
        }
    }

    /**
     * Clear all cart items and any applied coupon from the session.
     *
     * @return void
     */
    public function clear(): void
    {
        Session::forget($this->sessionKey);
        Session::forget('applied_coupon');
    }

    /**
     * Calculate and return the cart totals including subtotal, setup fee, discount, tax, and grand total.
     *
     * @param string|null $country
     * @return array
     */
    public function getTotals(?string $country = null): array
    {
        $items = $this->getItems();
        $subtotal = 0;
        $setupFee = 0;
        $eligibleForDiscountSubtotal = 0;

        $appliedCoupon = Session::get('applied_coupon');

        foreach ($items as $item) {
            $itemSubtotal = $item['unit_price'] * $item['quantity']; 
            $itemSetupFee = $item['setup_fee'] * $item['quantity'];
            $itemTotalBeforeDiscount = $itemSubtotal + $itemSetupFee;

            $subtotal += $itemSubtotal;
            $setupFee += $itemSetupFee;

            if ($appliedCoupon) {
                $isDomain = isset($item['type']) && $item['type'] === \App\OrderItemType::Domain->value;
                $hasPackages = !empty($appliedCoupon['allowed_packages']);
                $hasTlds = !empty($appliedCoupon['allowed_tlds'] ?? []);
                $allowedCycles = $appliedCoupon['allowed_cycles'] ?? [];

                if ($isDomain) {
                    if ($hasPackages && !$hasTlds) {
                        $itemEligible = false;
                    } else {
                        $itemEligible = !$hasTlds || in_array($item['tld_id'] ?? null, $appliedCoupon['allowed_tlds'] ?? []);
                    }
                    
                    $hasDomainCycle = false;
                    foreach ($allowedCycles as $cycle) {
                        if (str_contains(strtolower($cycle), 'year')) {
                            $hasDomainCycle = true;
                            break;
                        }
                    }
                    
                    if (!empty($allowedCycles) && $hasDomainCycle) {
                        $isCycleEligible = in_array($item['cycle_name'], $allowedCycles);
                    } else {
                        $isCycleEligible = true;
                    }
                } else {
                    if ($hasTlds && !$hasPackages) {
                        $itemEligible = false;
                    } else {
                        $itemEligible = !$hasPackages || in_array($item['package_id'] ?? null, $appliedCoupon['allowed_packages']);
                    }
                    $isCycleEligible = empty($allowedCycles) || in_array($item['cycle_name'], $allowedCycles);
                }

                if ($itemEligible && $isCycleEligible) {
                    $eligibleForDiscountSubtotal += $itemTotalBeforeDiscount;
                }
            }
        }

        $discount = 0;
        if ($appliedCoupon && $eligibleForDiscountSubtotal > 0) {
            if (strtolower($appliedCoupon['type']) === 'percentage') {
                $discount = ($eligibleForDiscountSubtotal * $appliedCoupon['value']) / 100;
            } else {
                $discount = min($appliedCoupon['value'], $eligibleForDiscountSubtotal);
            }
        }

        $taxAmount = 0;
        $taxModel = null;
        if ($country) {
            $taxModel = Tax::where('country', strtoupper($country))->first();
        }

        if (!$taxModel) {
            $taxModel = Tax::whereNull('country')->orWhere('country', '')->first();
        }

        if ($taxModel) {
            $taxableAmount = max(0, ($subtotal + $setupFee) - $discount);
            $taxAmount = ($taxableAmount * $taxModel->value) / 100;
        }

        $total = ($subtotal + $setupFee) - $discount + $taxAmount;

        return [
            'subtotal' => $subtotal,
            'setup_fee' => $setupFee,
            'discount' => $discount,
            'tax' => $taxAmount,
            'tax_name' => $taxModel ? $taxModel->name : null,
            'total' => max(0, $total),
            'count' => array_sum(array_column($items, 'quantity')),
        ];
    }

    /**
     * Update the prices of a specific cart item by its cart item ID.
     *
     * @param  string  $cartItemId
     * @param  float  $unitPrice
     * @param  float  $setupFee
     * @return void
     */
    public function updateItemPrices(string $cartItemId, float $unitPrice, float $setupFee): void
    {
        $cart = $this->getItems();

        if (isset($cart[$cartItemId])) {
            Session::put("{$this->sessionKey}.{$cartItemId}.unit_price", $unitPrice);
            Session::put("{$this->sessionKey}.{$cartItemId}.setup_fee", $setupFee);
        }
    }
}