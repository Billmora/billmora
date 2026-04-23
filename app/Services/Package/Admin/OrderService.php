<?php

namespace App\Services\Package\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\Registrant;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CouponUsage;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Coupon;
use App\Models\Tld;
use App\Models\VariantOption;
use App\OrderItemType;
use App\Services\Package\PricingService;
use Illuminate\Support\Facades\DB;
use Billmora;

class OrderService
{
    public function __construct(protected PricingService $pricingService)
    {
        // 
    }

    /**
     * Create a new order with multiple package items and/or domain items,
     * services, registrants, and invoice.
     *
     * @param  int  $userId
     * @param  array  $packageItems  Each: [package, price, variants, config, quantity]
     * @param  array  $domainItems   Each: [type, domain, tld, years, epp_code]
     * @param  \App\Models\Coupon|null  $coupon
     * @param  string  $currency
     * @param  string  $status
     * @return array{order: \App\Models\Order, services: array, registrants: array, invoice: \App\Models\Invoice}
     */
    public function createOrder(
        int $userId,
        array $packageItems,
        array $domainItems,
        ?Coupon $coupon,
        string $currency,
        string $status = 'pending',
    ): array {
        // Calculate all pricing upfront
        $packagePricingResults = [];
        $domainPricingResults = [];
        $grandSubtotal = 0;
        $grandDiscount = 0;
        $grandSetupFee = 0;
        $grandTotal = 0;

        foreach ($packageItems as $i => $item) {
            $pricing = $this->pricingService->calculatePricing(
                $item['price'],
                $item['variants'],
                $coupon,
                $currency
            );
            $qty = $item['quantity'] ?? 1;
            $packagePricingResults[$i] = $pricing;

            $grandSubtotal += $pricing['subtotal'] * $qty;
            $grandDiscount += $pricing['discount'] * $qty;
            $grandSetupFee += $pricing['setup_fee_total'] * $qty;
            $grandTotal += $pricing['total'] * $qty;
        }

        foreach ($domainItems as $i => $item) {
            $pricing = $this->pricingService->calculateDomainPricing(
                $item['tld'],
                $item['type'],
                $item['years'],
                $coupon,
                $currency
            );
            $domainPricingResults[$i] = $pricing;

            $grandSubtotal += $pricing['subtotal'];
            $grandDiscount += $pricing['discount'];
            $grandTotal += $pricing['total'];
        }

        return DB::transaction(function () use (
            $userId, $packageItems, $domainItems, $packagePricingResults, $domainPricingResults,
            $coupon, $currency, $status, $grandSubtotal, $grandDiscount, $grandSetupFee, $grandTotal,
        ) {
            // 1. Create Order
            $order = Order::create([
                'user_id' => $userId,
                'coupon_id' => $coupon?->id,
                'status' => $status,
                'currency' => $currency,
                'subtotal' => $grandSubtotal,
                'discount' => $grandDiscount,
                'setup_fee' => $grandSetupFee,
                'total' => $grandTotal,
                'terms_accepted' => true,
                'completed_at' => $status === 'completed' ? now() : null,
                'cancelled_at' => in_array($status, ['cancelled', 'failed']) ? now() : null,
            ]);

            $services = [];
            $registrants = [];
            $invoiceItems = [];

            // 2. Process Package Items
            foreach ($packageItems as $i => $item) {
                $package = $item['package'];
                $packagePrice = $item['price'];
                $variantSelections = $item['variants'];
                $configuration = $item['config'] ?? [];
                $quantity = $item['quantity'] ?? 1;
                $pricing = $packagePricingResults[$i];

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => OrderItemType::Service->value,
                    'item_id' => $package->id,
                    'description' => $package->name,
                    'quantity' => $quantity,
                    'billing_type' => $packagePrice->type,
                    'billing_interval' => $packagePrice->time_interval,
                    'billing_period' => $packagePrice->billing_period,
                    'unit_price' => $pricing['recurring_total'],
                    'setup_fee' => $pricing['setup_fee_total'],
                    'amount' => ($pricing['recurring_total'] * $quantity) + ($pricing['setup_fee_total'] * $quantity),
                    'config_options' => $configuration,
                    'variant_selections' => $variantSelections,
                ]);

                $serviceStatus = match ($status) {
                    'completed' => 'active',
                    'cancelled', 'failed' => 'cancelled',
                    default => 'pending'
                };

                $activatedAt = null;
                $nextDueDate = null;

                if ($packagePrice->type === 'recurring' && $serviceStatus === 'active') {
                    $activatedAt = now();
                    $date = now();
                    $nextDueDate = match ($packagePrice->billing_period) {
                        'daily' => $date->copy()->addDays($packagePrice->time_interval),
                        'weekly' => $date->copy()->addWeeks($packagePrice->time_interval),
                        'monthly' => $date->copy()->addMonths($packagePrice->time_interval),
                        'yearly' => $date->copy()->addYears($packagePrice->time_interval),
                        default => null,
                    };
                } elseif ($serviceStatus === 'pending') {
                    $nextDueDate = now();
                }

                $finalConfig = $this->resolveServiceConfiguration($package, $variantSelections, $configuration);

                for ($j = 0; $j < $quantity; $j++) {
                    $services[] = Service::create([
                        'user_id' => $userId,
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'package_id' => $package->id,
                        'package_price_id' => $packagePrice->id,
                        'plugin_id' => $package->plugin_id,
                        'name' => $package->name,
                        'status' => $serviceStatus,
                        'currency' => $currency,
                        'billing_type' => $packagePrice->type,
                        'billing_interval' => $packagePrice->time_interval,
                        'billing_period' => $packagePrice->billing_period,
                        'price' => $pricing['recurring_total'],
                        'setup_fee' => $pricing['setup_fee_total'],
                        'configuration' => $finalConfig,
                        'variant_selections' => $variantSelections,
                        'activated_at' => $activatedAt,
                        'next_due_date' => $nextDueDate,
                        'cancelled_at' => $serviceStatus === 'cancelled' ? now() : null,
                    ]);
                }

                // Build invoice items for this package
                $invoiceItems[] = [
                    'description' => $this->buildPackageDescription($package, $packagePrice),
                    'quantity' => $quantity,
                    'unit_price' => $pricing['base_price'],
                    'amount' => $pricing['base_price'] * $quantity,
                ];

                foreach ($pricing['variant_items'] as $variantItem) {
                    if ($variantItem['price'] <= 0) continue;
                    $invoiceItems[] = [
                        'description' => $variantItem['description'],
                        'quantity' => $quantity,
                        'unit_price' => $variantItem['price'],
                        'amount' => $variantItem['price'] * $quantity,
                    ];
                }

                if ($pricing['setup_fee_package'] > 0) {
                    $invoiceItems[] = [
                        'description' => 'Setup Fee - ' . $package->name,
                        'quantity' => $quantity,
                        'unit_price' => $pricing['setup_fee_package'],
                        'amount' => $pricing['setup_fee_package'] * $quantity,
                    ];
                }

                foreach ($pricing['setup_fee_variants'] as $sfItem) {
                    if ($sfItem['amount'] <= 0) continue;
                    $invoiceItems[] = [
                        'description' => 'Setup Fee - ' . $sfItem['description'],
                        'quantity' => $quantity,
                        'unit_price' => $sfItem['amount'],
                        'amount' => $sfItem['amount'] * $quantity,
                    ];
                }
            }

            // 3. Process Domain Items
            foreach ($domainItems as $i => $item) {
                $tld = $item['tld'];
                $pricing = $domainPricingResults[$i];

                $configOptions = [
                    'domain' => $item['domain'],
                    'type' => $item['type'],
                    'years' => $item['years'],
                ];

                if ($item['type'] === 'transfer' && !empty($item['epp_code'])) {
                    $configOptions['epp_code'] = $item['epp_code'];
                }

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => OrderItemType::Domain->value,
                    'item_id' => $tld->id,
                    'description' => $item['domain'],
                    'quantity' => 1,
                    'billing_type' => 'onetime',
                    'billing_interval' => $item['years'],
                    'billing_period' => 'yearly',
                    'unit_price' => $pricing['total'],
                    'setup_fee' => 0,
                    'amount' => $pricing['total'],
                    'config_options' => $configOptions,
                    'variant_selections' => [],
                ]);

                $registrantStatus = match ($status) {
                    'completed' => 'active',
                    'cancelled', 'failed' => 'cancelled',
                    default => 'pending'
                };

                $registrant = Registrant::create([
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'tld_id' => $tld->id,
                    'plugin_id' => $tld->plugin_id ?? null,
                    'domain' => $item['domain'],
                    'status' => $item['type'] === 'transfer' ? 'pending_transfer' : $registrantStatus,
                    'registration_type' => $item['type'],
                    'years' => $item['years'],
                    'currency' => $currency,
                    'price' => $pricing['total'],
                    'registered_at' => $registrantStatus === 'active' ? now() : null,
                    'expires_at' => $registrantStatus === 'active'
                        ? now()->addYears($item['years'])
                        : null,
                ]);

                $registrants[] = $registrant;

                // Build invoice item for this domain
                $typeLabel = ucfirst($item['type']);
                $invoiceItems[] = [
                    'description' => "{$typeLabel} - {$item['domain']} ({$item['years']} year" . ($item['years'] > 1 ? 's' : '') . ")",
                    'quantity' => 1,
                    'unit_price' => $pricing['total'],
                    'amount' => $pricing['total'],
                ];
            }

            // 4. Handle discount invoice item
            if ($grandDiscount > 0 && $coupon) {
                $invoiceItems[] = [
                    'description' => 'Discount - Coupon: ' . $coupon->code,
                    'quantity' => 1,
                    'unit_price' => -$grandDiscount,
                    'amount' => -$grandDiscount,
                ];
            }

            // 5. Create Invoice
            $invoiceStatus = match ($status) {
                'completed' => 'paid',
                'cancelled' => 'cancelled',
                default => 'unpaid'
            };

            $invoiceDays = (int) (Billmora::getAutomation('invoice_generation_days') ?? 7);

            $invoice = Invoice::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'status' => $invoiceStatus,
                'currency' => $currency,
                'subtotal' => $grandSubtotal,
                'discount' => $grandDiscount,
                'total' => $grandTotal,
                'due_date' => now()->addDays($invoiceDays),
                'paid_at' => $status === 'completed' ? now() : null,
            ]);

            // 6. Create Invoice Items
            foreach ($invoiceItems as $invItem) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => null,
                    'description' => $invItem['description'],
                    'quantity' => $invItem['quantity'],
                    'unit_price' => $invItem['unit_price'],
                    'amount' => $invItem['amount'],
                ]);
            }

            // 7. Record coupon usage
            if ($coupon) {
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'used_at' => now(),
                ]);
            }

            return [
                'order' => $order,
                'services' => $services,
                'registrants' => $registrants,
                'invoice' => $invoice,
            ];
        });
    }

    /**
     * Update the order status and synchronize associated services, registrants,
     * and invoice status accordingly.
     *
     * @param  \App\Models\Order  $order
     * @param  string  $newStatus
     * @return \App\Models\Order
     */
    public function updateOrderStatus(Order $order, string $newStatus): Order
    {
        return DB::transaction(function () use ($order, $newStatus) {
            $order->update([
                'status' => $newStatus,
                'completed_at' => $newStatus === 'completed' ? now() : null,
                'cancelled_at' => in_array($newStatus, ['cancelled', 'failed']) ? now() : null,
            ]);

            // Sync services
            foreach ($order->services as $service) {
                if ($newStatus === 'completed') {
                    if ($service->status !== 'active') {
                        $service->forceFill(['cancelled_at' => null])->save(); 
                        $service->activate();
                    }
                } elseif (in_array($newStatus, ['cancelled', 'failed'])) {
                    $service->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ]);
                } else {
                    $service->update([
                        'status' => 'pending',
                        'cancelled_at' => null,
                        'terminated_at' => null,
                        'activated_at' => null, 
                        'next_due_date' => null,
                    ]);
                }
            }

            // Sync registrants
            foreach ($order->registrants as $registrant) {
                if ($newStatus === 'completed') {
                    if ($registrant->status === 'pending') {
                        $registrant->activate();
                    }
                } elseif (in_array($newStatus, ['cancelled', 'failed'])) {
                    $registrant->cancel();
                } else {
                    $registrant->update([
                        'status' => 'pending',
                        'cancelled_at' => null,
                    ]);
                }
            }

            if ($order->invoice) {
                $invoiceStatus = match($newStatus) {
                    'completed' => 'paid',
                    'cancelled' => 'cancelled',
                    default => 'unpaid'
                };
                
                $order->invoice->update([
                    'status' => $invoiceStatus,
                    'paid_at' => $newStatus === 'completed' ? now() : null,
                ]);
            }

            return $order;
        });
    }

    /**
     * Build a human-readable invoice description for the package including billing period date range.
     *
     * @param  \App\Models\Package  $package
     * @param  \App\Models\PackagePrice  $packagePrice
     * @return string
     */
    private function buildPackageDescription(Package $package, PackagePrice $packagePrice): string
    {
        $description = "{$package->catalog->name} - {$package->name}";
        if ($packagePrice->type === 'recurring') {
            $start = now();
            $end = $start->copy();
            switch ($packagePrice->billing_period) {
                case 'daily': $end->addDays($packagePrice->time_interval); break;
                case 'weekly': $end->addWeeks($packagePrice->time_interval); break;
                case 'monthly': $end->addMonths($packagePrice->time_interval); break;
                case 'yearly': $end->addYears($packagePrice->time_interval); break;
            }
            $description .= " (" . $start->format('d/m/Y') . " - " . $end->format('d/m/Y') . ")";
        }
        return $description;
    }

    /**
     * Resolve the final service configuration by merging package defaults, variant values, and checkout input.
     *
     * @param  \App\Models\Package  $package
     * @param  array  $variantSelections
     * @param  array  $checkoutConfiguration
     * @return array
     */
    protected function resolveServiceConfiguration(Package $package, array $variantSelections, array $checkoutConfiguration = []): array
    {
        $config = $package->provisioning_config ?? [];
        if (!empty($variantSelections)) {
            $optionIds = collect($variantSelections)->flatten()->filter()->toArray();
            if (!empty($optionIds)) {
                $options = VariantOption::with('variant')->whereIn('id', $optionIds)->get();
                foreach ($options as $option) {
                    $key = $option->variant->code ?? null;
                    if (empty($key)) continue;
                    $value = $option->value;
                    if (is_numeric($value)) $value = $value + 0;
                    elseif (strtolower($value) === 'true') $value = true;
                    elseif (strtolower($value) === 'false') $value = false;
                    $config[$key] = $value;
                }
            }
        }
        if (!empty($checkoutConfiguration)) {
            $config = array_merge($config, $checkoutConfiguration);
        }
        return $config;
    }
}