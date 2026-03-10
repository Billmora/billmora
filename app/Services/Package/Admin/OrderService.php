<?php

namespace App\Services\Package\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CouponUsage;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Coupon;
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
     * Create a new order, services, and invoice for a package with pricing, coupon, and quantity support.
     *
     * @param  int  $userId
     * @param  \App\Models\Package  $package
     * @param  \App\Models\PackagePrice  $packagePrice
     * @param  array  $variantSelections
     * @param  \App\Models\Coupon|null  $coupon
     * @param  string  $currency
     * @param  string  $status
     * @param  array  $configuration
     * @param  int  $quantity
     * @return array{order: \App\Models\Order, services: array, invoice: \App\Models\Invoice}
     */
    public function createOrder(
        int $userId,
        Package $package,
        PackagePrice $packagePrice,
        array $variantSelections,
        ?Coupon $coupon,
        string $currency,
        string $status = 'pending',
        array $configuration = [],
        int $quantity = 1
    ): array {
        $pricing = $this->pricingService->calculatePricing(
            $packagePrice,
            $variantSelections,
            $coupon,
            $currency
        );

        return DB::transaction(function () use (
            $userId, $package, $packagePrice, $variantSelections, $pricing,
            $coupon, $currency, $status, $configuration, $quantity,
        ) {
            $order = Order::create([
                'user_id' => $userId,
                'coupon_id' => $coupon?->id,
                'status' => $status,
                'currency' => $currency,
                'subtotal' => $pricing['subtotal'] * $quantity,
                'discount' => $pricing['discount'] * $quantity,
                'setup_fee' => $pricing['setup_fee_total'] * $quantity,
                'total' => $pricing['total'] * $quantity,
                'terms_accepted' => true,
                'completed_at' => $status === 'completed' ? now() : null,
                'cancelled_at' => in_array($status, ['cancelled', 'failed']) ? now() : null,
            ]);

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'item_type' => OrderItemType::Service->value,
                'item_id' => $package->id,
                'name' => $package->name,
                'quantity' => $quantity,
                'billing_type' => $packagePrice->type,
                'billing_interval' => $packagePrice->time_interval,
                'billing_period' => $packagePrice->billing_period,
                'price' => $pricing['recurring_total'],
                'setup_fee' => $pricing['setup_fee_total'],
                'config_options' => $configuration,
                'variant_selections' => $variantSelections,
            ]);

            $serviceStatus = match($status) {
                'completed' => 'active',
                'cancelled', 'failed' => 'cancelled',
                default => 'pending'
            };

            $activatedAt = null;
            $nextDueDate = null;

            if ($packagePrice->type === 'recurring' && $serviceStatus === 'active') {
                $activatedAt = now();
                $date = now();
                $nextDueDate = match($packagePrice->billing_period) {
                    'daily'   => $date->copy()->addDays($packagePrice->time_interval),
                    'weekly'  => $date->copy()->addWeeks($packagePrice->time_interval),
                    'monthly' => $date->copy()->addMonths($packagePrice->time_interval),
                    'yearly'  => $date->copy()->addYears($packagePrice->time_interval),
                    default   => null,
                };
            } elseif ($serviceStatus === 'pending') {
                $nextDueDate = now();
            }

            $finalConfig = $this->resolveServiceConfiguration($package, $variantSelections, $configuration);

            $services = [];
            for ($i = 0; $i < $quantity; $i++) {
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

            $invoiceStatus = match($status) {
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
                'subtotal' => $pricing['subtotal'] * $quantity,
                'discount' => $pricing['discount'] * $quantity,
                'total' => $pricing['total'] * $quantity,
                'due_date' => now()->addDays($invoiceDays),
                'paid_at' => $status === 'completed' ? now() : null,
            ]);

            $this->createInvoiceItems($invoice, $package, $packagePrice, $pricing, $coupon, $quantity);

            if ($coupon) {
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'used_at' => now(),
                ]);
            }

            return ['order' => $order, 'services' => $services, 'invoice' => $invoice];
        });
    }

    /**
     * Update the order status and synchronize associated services and invoice status accordingly.
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
     * Create and persist invoice line items including variants, setup fees, and discount entries.
     *
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Models\Package  $package
     * @param  \App\Models\PackagePrice  $packagePrice
     * @param  array  $pricing
     * @param  \App\Models\Coupon|null  $coupon
     * @param  int  $quantity
     * @return void
     */
    private function createInvoiceItems(
        Invoice $invoice,
        Package $package,
        PackagePrice $packagePrice,
        array $pricing,
        ?Coupon $coupon,
        int $quantity
    ): void {
        $description = $this->buildPackageDescription($package, $packagePrice);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'service_id' => null,
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $pricing['base_price'],
            'amount' => $pricing['base_price'] * $quantity,
        ]);

        foreach ($pricing['variant_items'] as $item) {
            if ($item['price'] <= 0) continue;
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => null,
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $item['price'],
                'amount' => $item['price'] * $quantity,
            ]);
        }

        if ($pricing['setup_fee_package'] > 0) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => null,
                'description' => 'Setup Fee - ' . $package->name,
                'quantity' => $quantity,
                'unit_price' => $pricing['setup_fee_package'],
                'amount' => $pricing['setup_fee_package'] * $quantity,
            ]);
        }

        foreach ($pricing['setup_fee_variants'] as $item) {
            if ($item['amount'] <= 0) continue;
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => null,
                'description' => 'Setup Fee - ' . $item['description'],
                'quantity' => $quantity,
                'unit_price' => $item['amount'],
                'amount' => $item['amount'] * $quantity,
            ]);
        }

        if ($pricing['discount'] > 0 && $coupon) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => null,
                'description' => 'Discount - Coupon: ' . $coupon->code,
                'quantity' => 1,
                'unit_price' => -($pricing['discount'] * $quantity),
                'amount' => -($pricing['discount'] * $quantity),
            ]);
        }
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