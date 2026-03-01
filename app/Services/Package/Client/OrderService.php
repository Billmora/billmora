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
use App\Models\VariantOption;
use App\Services\Package\PricingService;
use App\Traits\AuditsSystem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    use AuditsSystem;

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
     * Create a new order, service, and invoice with pricing, configuration, and coupon usage.
     *
     * @param int $userId
     * @param int $packagePriceId
     * @param array<int|string, mixed> $variantSelections
     * @param \App\Models\Coupon|null $coupon
     * @param array<string, mixed> $checkoutData
     * @param array<string, mixed> $configuration
     * @return array{order:\App\Models\Order,service:\App\Models\Service,invoice:\App\Models\Invoice}
     */
    public function createOrder(
        int $userId,
        int $packagePriceId,
        array $variantSelections,
        ?Coupon $coupon,
        array $checkoutData,
        array $configuration = [],
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
            $currency,
            $configuration,
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

            $this->recordCreate('order.created', $order->toArray());

            $initialDueDate = ($packagePrice->type === 'recurring') ? now() : null;
            $finalConfig = $this->resolveServiceConfiguration($package, $variantSelections, $configuration);

            $service = Service::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'package_id' => $package->id,
                'package_price_id' => $packagePrice->id,
                'plugin_id' => $package->plugin_id,
                'name' => $package->name,
                'status' => 'pending',
                'currency' => $currency,
                'billing_type' => $packagePrice->type,
                'billing_interval' => $packagePrice->time_interval,
                'billing_period' => $packagePrice->billing_period,
                'price' => $pricing['recurring_total'],
                'setup_fee' => $pricing['setup_fee_total'],
                'next_due_date' => $initialDueDate,
                'configuration' => $finalConfig, 
                'variant_selections' => $variantSelections,
            ]);

            $this->recordCreate('service.created', $service->toArray());

            $invoice = Invoice::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'plugin_id' => $checkoutData['payment_method'] ?? null,
                'status' => 'unpaid',
                'currency' => $currency,
                'subtotal' => $pricing['subtotal'],
                'discount' => $pricing['discount'],
                'setup_fee' => $pricing['setup_fee_total'],
                'total' => $pricing['total'],
                'due_date' => now()->addDays(7),
            ]);

            $this->recordCreate('invoice.created', $invoice->toArray());

            $this->createInvoiceItems($invoice, $service, $package, $packagePrice, $pricing, $coupon);

            if ($coupon) {
                $coupon = CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'used_at' => now(),
                ]);

                $this->recordCreate('coupon.used', $coupon->toArray());
            }

            return compact('order', 'service', 'invoice');
        });
    }

    /**
     * Create invoice items for base package, variants, setup fees, and discount lines.
     *
     * @param \App\Models\Invoice $invoice
     * @param \App\Models\Service $service
     * @param \App\Models\Package $package
     * @param \App\Models\PackagePrice $packagePrice
     * @param array<string, mixed> $pricing
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
     * Build main package invoice description including catalog and service period.
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
     * Calculate formatted service period range string from billing period and interval.
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

    /**
     * Resolve final service configuration from package defaults, variants, and checkout data.
     *
     * @param \App\Models\Package $package
     * @param array<int|string, mixed> $variantSelections
     * @param array<string, mixed> $checkoutConfiguration
     * @return array<string, mixed>
     */
    protected function resolveServiceConfiguration(Package $package, array $variantSelections, array $checkoutConfiguration = []): array
    {
        $config = $package->provisioning_config ?? [];

        if (!empty($variantSelections)) {
            $optionIds = collect($variantSelections)->flatten()->filter()->toArray();
            
            if (!empty($optionIds)) {
                $options = VariantOption::with('variant')
                    ->whereIn('id', $optionIds)
                    ->get();

                foreach ($options as $option) {
                    $key = $option->variant->code ?? null;
                    if (empty($key)) continue;

                    $value = $option->value;
                    if (is_numeric($value)) {
                        $value = $value + 0;
                    } elseif (strtolower($value) === 'true') {
                        $value = true;
                    } elseif (strtolower($value) === 'false') {
                        $value = false;
                    }

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
