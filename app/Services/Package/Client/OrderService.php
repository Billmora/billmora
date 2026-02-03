<?php

namespace App\Services\Package\Client;

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
     * Create order, service, and invoice from checkout session data.
     *
     * @param int $userId
     * @param int $packagePriceId
     * @param array $variantSelections
     * @param \App\Models\Coupon|null $coupon
     * @param array $checkoutData
     * @return array
     */
    public function createFromCheckout(
        int $userId,
        int $packagePriceId,
        array $variantSelections,
        ?Coupon $coupon,
        array $checkoutData
    ): array {
        $packagePrice = PackagePrice::with('package.catalog')->findOrFail($packagePriceId);
        $package = $packagePrice->package;
        $currency = session('currency');

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
     * Build package description with catalog name and service period.
     *
     * @param \App\Models\Package $package
     * @param \App\Models\PackagePrice $packagePrice
     * @return string
     */
    private function buildPackageDescription(Package $package, PackagePrice $packagePrice): string
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
     * Calculate service period date range based on billing interval.
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
