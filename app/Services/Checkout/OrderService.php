<?php

namespace App\Services\Checkout;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\PackagePrice;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class OrderService
{

    /**
     * Process and create a new order with selected package, variants, and pricing details.
     *
     * @param int $userId
     * @param \App\Models\PackagePrice $packagePrice
     * @param array $variantSelections
     * @param array $pricing
     * @param \App\Models\Coupon|null $coupon
     * @param array $checkoutData
     * @return mixed
     */
    public function process(int $userId, PackagePrice $packagePrice, array $variantSelections, array $pricing, ?Coupon $coupon, array $checkoutData)
    {
        $package = $packagePrice->package;
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
                'description' => $package->name . ' - ' . $packagePrice->name,
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
                    'description' => 'Variant Option - ' . $item['description'],
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

            // Update order status (can be adjusted according to business logic)
            // For now, keep pending until payment is completed
            // $order->markAsCompleted();

            return compact('order', 'service', 'invoice');
        });
    }
}
