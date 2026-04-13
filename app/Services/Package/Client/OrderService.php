<?php

namespace App\Services\Package\Client;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CouponUsage;
use App\Models\Package;
use App\Models\Registrant;
use App\Models\Tld;
use App\OrderItemType;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Support\Facades\DB;

class OrderService
{
    use AuditsSystem;

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

            foreach ($cartItems as $item) {
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
                ]);

                if ($item['type'] === OrderItemType::Domain->value) {
                    $domainName = $item['config_options']['domain'] ?? '';
                    $tldId = $item['tld_id'] ?? null;
                    $regType = $item['config_options']['type'] ?? 'register';
                    $years = $item['config_options']['years'] ?? 1;
                    $eppCode = $item['config_options']['epp_code'] ?? null;
                    $tldModel = Tld::find($tldId);

                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $registrant = Registrant::create([
                            'user_id' => $userId,
                            'order_id' => $order->id,
                            'order_item_id' => $orderItem->id,
                            'tld_id' => $tldId,
                            'plugin_id' => $tldModel->plugin_id ?? null,
                            'domain' => $domainName,
                            'status' => 'pending',
                            'registration_type' => $regType,
                            'years' => $years,
                            'currency' => $currency,
                            'price' => $item['unit_price'],
                            'epp_code' => $eppCode,
                        ]);

                        $this->recordCreate('registrant.created', $registrant->toArray());
                    }
                } else {
                    $package = Package::find($item['package_id']);
                    $initialDueDate = ($item['billing_type'] === 'recurring') ? now() : null;

                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $service = Service::create([
                            'user_id' => $userId,
                            'order_id' => $order->id,
                            'order_item_id' => $orderItem->id,
                            'package_id' => $item['package_id'],
                            'package_price_id' => $item['package_price_id'],
                            'plugin_id' => $package->plugin_id ?? null,
                            'name' => $item['description'],
                            'status' => 'pending',
                            'currency' => $currency,
                            'billing_type' => $item['billing_type'],
                            'billing_interval' => $item['billing_interval'],
                            'billing_period' => $item['billing_period'],
                            'price' => $item['unit_price'],
                            'setup_fee' => $item['setup_fee'],
                            'next_due_date' => $initialDueDate,
                            'configuration' => $item['config_options'],
                            'variant_selections' => $item['variant_selections'],
                        ]);

                        $this->recordCreate('service.created', $service->toArray());
                    }
                }
            }

            $invoiceDueDate = (int) Billmora::getAutomation('invoice_generation_days');

            $invoice = Invoice::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'plugin_id' => $checkoutData['payment_method'] ?? null,
                'status' => 'unpaid',
                'currency' => $currency,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'setup_fee' => $totals['setup_fee'],
                'tax' => $totals['tax'] ?? 0,
                'total' => $totals['total'],
                'due_date' => now()->addDays($invoiceDueDate),
            ]);

            $this->recordCreate('invoice.created', $invoice->toArray());

            $this->createInvoiceItems($invoice, $cartItems, $totals, $appliedCoupon);

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
    private function createInvoiceItems(Invoice $invoice, array $cartItems, array $totals, ?array $appliedCoupon): void
    {
        foreach ($cartItems as $item) {
            $description = $item['description'];

            if ($item['billing_type'] === 'recurring') {
                $startDate = now();
                $endDate = $startDate->copy();
                
                switch ($item['billing_period']) {
                    case 'daily':
                        $endDate->addDays($item['billing_interval']);
                        break;
                    case 'weekly':
                        $endDate->addWeeks($item['billing_interval']);
                        break;
                    case 'monthly':
                        $endDate->addMonths($item['billing_interval']);
                        break;
                    case 'yearly':
                        $endDate->addYears($item['billing_interval']);
                        break;
                }

                $description .= " (" . $startDate->format('d/m/Y') . " - " . $endDate->format('d/m/Y') . ")";
            }

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => null,
                'description' => $description,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['unit_price'] * $item['quantity'],
            ]);

            if ($item['setup_fee'] > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => null,
                    'description' => "Setup Fee - {$item['description']}",
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['setup_fee'],
                    'amount' => $item['setup_fee'] * $item['quantity'],
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