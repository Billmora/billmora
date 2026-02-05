<?php

namespace App\Services\Package\Admin;

use App\Models\Order;
use App\Models\Service;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CouponUsage;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Coupon;
use App\Services\Package\PricingService;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * The pricing service instance.
     *
     * @var \App\Services\Package\PricingService
     */
    protected $pricingService;

    /**
     * Create a new order service instance.
     *
     * @param \App\Services\Package\PricingService $pricingService
     * @return void
     */
    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Create a new order with associated service and invoice.
     *
     * @param int $userId
     * @param \App\Models\Package $package
     * @param \App\Models\PackagePrice $packagePrice
     * @param array $variantSelections
     * @param \App\Models\Coupon|null $coupon
     * @param string $currency
     * @param string $status
     * @return array
     */
    public function createOrder(
        int $userId,
        Package $package,
        PackagePrice $packagePrice,
        array $variantSelections,
        ?Coupon $coupon,
        string $currency,
        string $status = 'pending'
    ): array {
        $pricing = $this->pricingService->calculatePricing(
            $packagePrice,
            $variantSelections,
            $coupon,
            $currency
        );

        return DB::transaction(function () use (
            $userId,
            $package,
            $packagePrice,
            $variantSelections,
            $pricing,
            $coupon,
            $currency,
            $status
        ) {
            $order = Order::create([
                'user_id' => $userId,
                'package_id' => $package->id,
                'package_price_id' => $packagePrice->id,
                'coupon_id' => $coupon?->id,
                'status' => $status,
                'currency' => $currency,
                'subtotal' => $pricing['subtotal'],
                'discount' => $pricing['discount'],
                'setup_fee' => $pricing['setup_fee_total'],
                'total' => $pricing['total'],
                'variant_selections' => $variantSelections,
                'terms_accepted' => true,
                'completed_at' => $status === 'completed' ? now() : null,
                'cancelled_at' => $status === 'cancelled' ? now() : null,
            ]);

            $serviceStatus = match($status) {
                'completed' => 'active',
                'cancelled', 'failed' => 'cancelled',
                default => 'pending'
            };

            $activatedAt = $serviceStatus === 'active' ? now() : null;
            $nextDueDate = null;

            if ($serviceStatus === 'active' && $packagePrice->type === 'recurring') {
                $date = now();
                $nextDueDate = match($packagePrice->billing_period) {
                    'daily' => $date->addDays($packagePrice->time_interval),
                    'weekly' => $date->addWeeks($packagePrice->time_interval),
                    'monthly' => $date->addMonths($packagePrice->time_interval),
                    'yearly' => $date->addYears($packagePrice->time_interval),
                    default => null,
                };
            }

            $service = Service::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'package_id' => $package->id,
                'package_price_id' => $packagePrice->id,
                'name' => $package->name,
                'status' => $serviceStatus,
                'currency' => $currency,
                'billing_type' => $packagePrice->type,
                'billing_interval' => $packagePrice->time_interval,
                'billing_period' => $packagePrice->billing_period,
                'price' => $pricing['recurring_total'],
                'setup_fee' => $pricing['setup_fee_total'],
                'variant_selections' => $variantSelections,
                'activated_at' => $activatedAt,
                'next_due_date' => $nextDueDate,
                'cancelled_at' => $serviceStatus === 'cancelled' ? now() : null,
            ]);

            $invoiceStatus = match($status) {
                'completed' => 'paid',
                'cancelled' => 'cancelled',
                default => 'unpaid'
            };

            $invoice = Invoice::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'status' => $invoiceStatus,
                'currency' => $currency,
                'subtotal' => $pricing['subtotal'],
                'discount' => $pricing['discount'],
                'total' => $pricing['total'],
                'due_date' => now()->addDays(7),
                'paid_at' => $status === 'completed' ? now() : null,
            ]);

            $this->createInvoiceItems($invoice, $service, $package, $packagePrice, $pricing, $coupon);

            if ($coupon) {
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'used_at' => now(),
                ]);
            }

            return compact('order', 'service', 'invoice');
        });
    }

    /**
     * Update order status and sync related service and invoice statuses.
     *
     * @param \App\Models\Order $order
     * @param string $newStatus
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

            if ($order->service) {
                if ($newStatus === 'completed') {
                    if ($order->service->status !== 'active') {
                        $order->service->forceFill(['cancelled_at' => null])->save(); 
                        $order->service->activate();
                    }
                } elseif (in_array($newStatus, ['cancelled', 'failed'])) {
                    $order->service->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ]);
                } else {
                    $order->service->update([
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
     * Create invoice items for package, variants, setup fees, and discount.
     *
     * @param \App\Models\Invoice $invoice
     * @param \App\Models\Service $service
     * @param \App\Models\Package $package
     * @param \App\Models\PackagePrice $packagePrice
     * @param array $pricing
     * @param \App\Models\Coupon|null $coupon
     * @return void
     */
    private function createInvoiceItems(
        Invoice $invoice,
        Service $service,
        Package $package,
        PackagePrice $packagePrice,
        array $pricing,
        ?Coupon $coupon
    ): void {
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'service_id' => $service->id,
            'description' => $this->buildPackageDescription($package, $packagePrice),
            'quantity' => 1,
            'unit_price' => $pricing['base_price'],
            'amount' => $pricing['base_price'],
        ]);

        foreach ($pricing['variant_items'] as $item) {
            if ($item['price'] <= 0) continue;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'description' => $item['description'],
                'quantity' => 1,
                'unit_price' => $item['price'],
                'amount' => $item['price'],
            ]);
        }

        if ($pricing['setup_fee_package'] > 0) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'description' => 'Setup Fee - ' . $package->name,
                'quantity' => 1,
                'unit_price' => $pricing['setup_fee_package'],
                'amount' => $pricing['setup_fee_package'],
            ]);
        }

        foreach ($pricing['setup_fee_variants'] as $item) {
            if ($item['amount'] <= 0) continue;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'description' => 'Setup Fee - ' . $item['description'],
                'quantity' => 1,
                'unit_price' => $item['amount'],
                'amount' => $item['amount'],
            ]);
        }

        if ($pricing['discount'] > 0 && $coupon) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'description' => 'Discount - Coupon: ' . $coupon->code,
                'quantity' => 1,
                'unit_price' => -$pricing['discount'],
                'amount' => -$pricing['discount'],
            ]);
        }
    }

    /**
     * Build package description with catalog and billing cycle information.
     *
     * @param \App\Models\Package $package
     * @param \App\Models\PackagePrice $packagePrice
     * @return string
     */
    private function buildPackageDescription(Package $package, PackagePrice $packagePrice): string
    {
        $catalogName = $package->catalog->name;
        $packageName = $package->name;
        
        $description = "{$catalogName} - {$packageName}";

        if (in_array(strtolower($packagePrice->type), ['recurring', 'onetime'])) {
            $period = $this->calculateServicePeriod($packagePrice);
            $description .= " ({$period})";
        }

        return $description;
    }

    /**
     * Calculate service period based on billing cycle.
     *
     * @param \App\Models\PackagePrice $packagePrice
     * @return string
     */
    private function calculateServicePeriod(PackagePrice $packagePrice): string
    {
        $start = now();
        
        $end = match(strtolower($packagePrice->billing_period)) {
            'daily' => $start->copy()->addDays($packagePrice->time_interval),
            'weekly' => $start->copy()->addWeeks($packagePrice->time_interval),
            'monthly' => $start->copy()->addMonths($packagePrice->time_interval),
            'yearly' => $start->copy()->addYears($packagePrice->time_interval),
            default => $start->copy()->addMonths(1),
        };

        return $start->toDateString() . ' - ' . $end->toDateString();
    }
}