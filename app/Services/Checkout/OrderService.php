<?php

namespace App\Services\Checkout;

use App\Models\CouponUsage;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\PackagePrice;
use App\Models\Service;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Process order creation with service and invoice
     * 
     * @param int $userId
     * @param PackagePrice $packagePrice
     * @param array $variantSelections
     * @param array $pricing
     * @param Coupon|null $coupon
     * @param array $checkoutData
     * @return array
     */
    public function process(int $userId, PackagePrice $packagePrice, array $variantSelections, array $pricing, ?Coupon $coupon, array $checkoutData)
    {
        $package = $packagePrice->package()->with('catalog')->first();
        $currency = session('currency');

        return DB::transaction(function () use (
            $userId,
            $package,
            $packagePrice,
            $variantSelections,
            $pricing,
            $coupon,
            $checkoutData,
            $currency
        ) {
            $order = Order::create([
                'user_id' => $userId,
                'package_id' => $package->id,
                'package_price_id' => $packagePrice->id,
                'coupon_id' => $coupon?->id,
                'status' => 'pending',
                'currency' => $currency,
                'subtotal' => $pricing['subtotal'],
                'discount' => $pricing['discount'],
                'setup_fee' => $pricing['setup_fee_total'],
                'total' => $pricing['total'],
                'notes' => $checkoutData['notes'] ?? null,
                'variant_selections' => $variantSelections,
                'terms_accepted' => $checkoutData['terms_accepted'] ?? true,
            ]);

            $service = Service::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'package_id' => $package->id,
                'package_price_id' => $packagePrice->id,
                'name' => $package->name,
                'status' => 'pending',
                'currency' => $currency,
                'billing_type' => $packagePrice->type,
                'billing_interval' => $packagePrice->time_interval,
                'billing_period' => $packagePrice->billing_period,
                'price' => $pricing['recurring_total'],
                'setup_fee' => $pricing['setup_fee_total'],
                'variant_selections' => $variantSelections,
                'configuration' => null,
            ]);

            $invoice = Invoice::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'status' => 'unpaid',
                'currency' => $currency,
                'subtotal' => $pricing['subtotal'],
                'discount' => $pricing['discount'],
                'total' => $pricing['total'],
                'due_date' => now()->addDays(7),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'description' => $this->buildPackageDescription($package, $packagePrice),
                'quantity' => 1,
                'unit_price' => $pricing['base_price'],
                'amount' => $pricing['base_price'],
            ]);

            foreach ($pricing['variant_items'] as $item) {
                if ($item['price'] <= 0) {
                    continue;
                }

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
                if ($item['amount'] <= 0) {
                    continue;
                }

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
     * Build package description with service period.
     *
     * @param \App\Models\Package $package
     * @param \App\Models\PackagePrice $packagePrice
     * @return string
     */
    private function buildPackageDescription($package, PackagePrice $packagePrice): string
    {
        $catalogName = $package->catalog->name ?? '';
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
            'hourly' => $start->copy()->addHours($packagePrice->time_interval),
            'daily' => $start->copy()->addDays($packagePrice->time_interval),
            'weekly' => $start->copy()->addWeeks($packagePrice->time_interval),
            'monthly' => $start->copy()->addMonths($packagePrice->time_interval),
            'yearly' => $start->copy()->addYears($packagePrice->time_interval),
            default => $start->copy()->addMonths(1),
        };

        return $start->toDateString() . ' - ' . $end->toDateString();
    }
}