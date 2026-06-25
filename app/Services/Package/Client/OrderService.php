<?php

namespace App\Services\Package\Client;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CouponUsage;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Registrant;
use App\Models\Tld;
use App\OrderItemType;
use App\Services\Package\ProrataService;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Support\Facades\DB;

class OrderService
{
    use AuditsSystem;

    public function __construct(protected ProrataService $prorataService) {}

    /**
     * Create a new master order, order items, services, and invoice from cart data.
     *
     * @param  int  $userId
     * @param  array  $cartItems
     * @param  array  $totals
     * @param  array|null  $appliedCoupon
     * @param  array  $checkoutData
     * @return array{order: \App\Models\Order, invoice: \App\Models\Invoice}
     */
    public function createOrder(
        int $userId,
        array $cartItems,
        array $totals,
        ?array $appliedCoupon,
        array $checkoutData
    ): array {
        $currency = session('currency');

        return DB::transaction(function () use (
            $userId,
            $cartItems,
            $totals,
            $appliedCoupon,
            $checkoutData,
            $currency,
        ) {
            $order = Order::create([
                'user_id' => $userId,
                'coupon_id' => $appliedCoupon['id'] ?? null,
                'status' => 'pending',
                'currency' => $currency,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'setup_fee' => $totals['setup_fee'],
                'tax' => $totals['tax'] ?? 0,
                'total' => $totals['total'],
                'notes' => $checkoutData['notes'] ?? null,
                'terms_accepted' => $checkoutData['terms_accepted'] ?? true,
            ]);

            $this->recordCreate('order.created', $order->toArray());

            $createdEntities = [];

            foreach ($cartItems as $cartItemId => $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => OrderItemType::tryFrom($item['type']),
                    'item_id' => $item['package_id'] ?? $item['tld_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'billing_type' => $item['billing_type'],
                    'billing_interval' => $item['billing_interval'],
                    'billing_period' => $item['billing_period'],
                    'unit_price' => $item['unit_price'],
                    'setup_fee' => $item['setup_fee'],
                    'amount' => ($item['unit_price'] * $item['quantity']) + ($item['setup_fee'] * $item['quantity']),
                    'config_options' => $item['config_options'],
                    'variant_selections' => $item['variant_selections'],
                    'fields' => $item['fields'] ?? [],
                ]);

                if ($item['type'] === OrderItemType::Domain->value) {
                    $domainName = $item['config_options']['domain'] ?? '';
                    $tldId = $item['tld_id'] ?? null;
                    $regType = $item['config_options']['type'] ?? 'register';
                    $years = $item['config_options']['years'] ?? 1;
                    $eppCode = $item['config_options']['epp_code'] ?? null;
                    $nameservers = $item['config_options']['nameservers'] ?? [];
                    $tldModel = Tld::find($tldId);

                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $registrant = Registrant::create([
                            'user_id'           => $userId,
                            'order_id'          => $order->id,
                            'order_item_id'     => $orderItem->id,
                            'tld_id'            => $tldId,
                            'plugin_id'         => $tldModel->plugin_id ?? null,
                            'domain'            => $domainName,
                            'status'            => 'pending',
                            'registration_type' => $regType,
                            'years'             => $years,
                            'currency'          => $currency,
                            'price'             => $item['unit_price'],
                            'nameservers'       => $nameservers,
                        ]);

                        $this->recordCreate('registrant.created', $registrant->toArray());
                        
                        if ($i === 0) {
                            $createdEntities[$cartItemId] = ['type' => 'registrant', 'id' => $registrant->id];
                        }
                    }
                } else {
                    $package = Package::find($item['package_id']);
                    $packagePrice = PackagePrice::find($item['package_price_id']);
                    $recurringTotal = $item['unit_price'];

                    // Calculate pro-rata for first billing cycle if applicable.
                    $prorata = ($item['billing_type'] === 'recurring' && $package && $packagePrice)
                        ? $this->prorataService->calculate($package, $packagePrice, $recurringTotal)
                        : null;

                    // When pro-rata applies, the service's initial next_due_date becomes the
                    // end of the first full period (prorata_day_date + interval), NOT now().
                    // When not applicable, keep the existing behaviour (now() for recurring).
                    $initialDueDate = $prorata
                        ? $prorata['first_next_due_date']
                        : (($item['billing_type'] === 'recurring') ? now() : null);

                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $service = Service::create([
                            'user_id'            => $userId,
                            'order_id'           => $order->id,
                            'order_item_id'      => $orderItem->id,
                            'package_id'         => $item['package_id'],
                            'package_price_id'   => $item['package_price_id'],
                            'plugin_id'          => $package->plugin_id ?? null,
                            'name'               => $item['description'],
                            'status'             => 'pending',
                            'currency'           => $currency,
                            'billing_type'       => $item['billing_type'],
                            'billing_interval'   => $item['billing_interval'],
                            'billing_period'     => $item['billing_period'],
                            'price'              => $item['unit_price'],
                            'setup_fee'          => $item['setup_fee'],
                            'next_due_date'      => $initialDueDate,
                            'configuration'      => $item['config_options'],
                            'variant_selections' => $item['variant_selections'],
                            'fields'             => $item['fields'] ?? [],
                        ]);

                        $this->recordCreate('service.created', $service->toArray());

                        if ($i === 0) {
                            $createdEntities[$cartItemId] = [
                                'type'    => 'service',
                                'id'      => $service->id,
                                'prorata' => $prorata,
                            ];
                        }
                    }
                }
            }

            $invoiceDueDate = (int) Billmora::getAutomation('invoice_generation_days');

            $invoice = Invoice::create([
                'user_id'   => $userId,
                'order_id'  => $order->id,
                'status'    => 'unpaid',
                'currency'  => $currency,
                'subtotal'  => $totals['subtotal'],
                'discount'  => $totals['discount'],
                'setup_fee' => $totals['setup_fee'],
                'tax'       => $totals['tax'] ?? 0,
                'total'     => $totals['total'],
                'due_date'  => now()->addDays($invoiceDueDate),
            ]);

            $this->recordCreate('invoice.created', $invoice->toArray());

            $this->createInvoiceItems($invoice, $cartItems, $totals, $appliedCoupon, $createdEntities);

            if ($appliedCoupon) {
                $couponUsage = CouponUsage::create([
                    'coupon_id' => $appliedCoupon['id'],
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'used_at' => now(),
                ]);

                $this->recordCreate('coupon.used', $couponUsage->toArray());
            }

            return compact('order', 'invoice');
        });
    }

    /**
     * Create and persist invoice line items including setup fees and discount entries from cart data.
     *
     * @param  \App\Models\Invoice  $invoice
     * @param  array  $cartItems
     * @param  array  $totals
     * @param  array|null  $appliedCoupon
     * @return void
     */
    private function createInvoiceItems(Invoice $invoice, array $cartItems, array $totals, ?array $appliedCoupon, array $createdEntities = []): void
    {
        foreach ($cartItems as $cartItemId => $item) {
            $description = $item['description'];

            $entity       = $createdEntities[$cartItemId] ?? null;
            $serviceId    = $entity && $entity['type'] === 'service'     ? $entity['id'] : null;
            $registrantId = $entity && $entity['type'] === 'registrant'  ? $entity['id'] : null;
            $prorata      = $entity['prorata'] ?? null;

            if ($prorata) {
                // --- Pro-rata first invoice: two line items ---
                $prorataDayDate  = $prorata['prorata_day_date'];
                $firstNextDue    = $prorata['first_next_due_date'];
                $fmt             = 'd M Y';

                // 1. Prorated line item (partial period)
                InvoiceItem::create([
                    'invoice_id'    => $invoice->id,
                    'service_id'    => $serviceId,
                    'registrant_id' => $registrantId,
                    'description'   => __('client/invoices.prorated_item', [
                        'item' => $description,
                        'start' => now()->format($fmt),
                        'end' => $prorataDayDate->format($fmt)
                    ]),
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $prorata['prorated_amount'],
                    'amount'        => $prorata['prorated_amount'] * $item['quantity'],
                ]);

                // 2. Full-period line item
                InvoiceItem::create([
                    'invoice_id'    => $invoice->id,
                    'service_id'    => $serviceId,
                    'registrant_id' => $registrantId,
                    'description'   => sprintf(
                        '%s (%s – %s)',
                        $description,
                        $prorataDayDate->format($fmt),
                        $firstNextDue->format($fmt)
                    ),
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $prorata['full_period_amount'],
                    'amount'        => $prorata['full_period_amount'] * $item['quantity'],
                ]);
            } else {
                if ($item['billing_type'] === 'recurring') {
                    $startDate = now();
                    $endDate   = $startDate->copy();

                    match ($item['billing_period']) {
                        'daily'   => $endDate->addDays($item['billing_interval']),
                        'weekly'  => $endDate->addWeeks($item['billing_interval']),
                        'monthly' => $endDate->addMonthsNoOverflow($item['billing_interval']),
                        'yearly'  => $endDate->addYears($item['billing_interval']),
                        default   => null,
                    };

                    $description .= ' (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
                }

                InvoiceItem::create([
                    'invoice_id'    => $invoice->id,
                    'service_id'    => $serviceId,
                    'registrant_id' => $registrantId,
                    'description'   => $description,
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['unit_price'],
                    'amount'        => $item['unit_price'] * $item['quantity'],
                ]);
            }

            if ($item['setup_fee'] > 0) {
                InvoiceItem::create([
                    'invoice_id'    => $invoice->id,
                    'service_id'    => $serviceId,
                    'registrant_id' => $registrantId,
                    'description'   => "Setup Fee - {$item['description']}",
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['setup_fee'],
                    'amount'        => $item['setup_fee'] * $item['quantity'],
                ]);
            }
        }

        if ($totals['discount'] > 0 && $appliedCoupon) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => null,
                'description' => 'Discount - Coupon: ' . $appliedCoupon['code'],
                'quantity' => 1,
                'unit_price' => -$totals['discount'],
                'amount' => -$totals['discount'],
            ]);
        }

        if (isset($totals['tax']) && $totals['tax'] > 0) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => null,
                'description' => $totals['tax_name'] ?? 'Tax',
                'quantity' => 1,
                'unit_price' => $totals['tax'],
                'amount' => $totals['tax'],
            ]);
        }
    }
}