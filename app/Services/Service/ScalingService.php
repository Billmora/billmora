<?php

namespace App\Services\Service;

use App\Models\Service;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ServiceScaling;
use App\Services\Package\PricingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ScalingService
{
    /**
     * Inject the PricingService dependency into the scaling service.
     *
     * @param  \App\Services\Package\PricingService  $pricingService
     */
    public function __construct(private PricingService $pricingService)
    {
        // 
    }

    /**
     * Validate that the service is eligible for scaling by checking status and unpaid invoices.
     *
     * @param  \App\Models\Service  $service
     * @return void
     *
     * @throws \Exception
     */
    public function validateRequest(Service $service): void
    {
        if ($service->status !== 'active') {
            throw new Exception(__('client/services.scaling.must_be_active'));
        }

        $hasUnpaid = Invoice::where('status', 'unpaid')
            ->whereHas('items', fn($q) => $q->where('service_id', $service->id))
            ->exists();

        if ($hasUnpaid) {
            throw new Exception(__('client/services.scaling.unpaid_invoice_exists'));
        }
    }

    /**
     * Retrieve all valid and filtered scaling candidate packages matching the service's current billing cycle and currency.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Support\Collection
     */
    public function getStrictCandidates(Service $service): Collection
    {
        $service->loadMissing('packagePrice');
        if (!$service->packagePrice) return collect();

        $currentPriceName = $service->packagePrice->name; 
        $currency = $service->currency;

        $candidates = $service->package->scalablePackages()
            ->where('packages.status', 'visible')
            ->with(['prices'])
            ->get();

        if ($service->package->status === 'visible' && !$candidates->contains('id', $service->package_id)) {
            $candidates->push($service->package);
        }

        $candidates = $candidates->sortBy('created_at')->values();

        return $candidates->filter(function ($pkg) use ($currency, $currentPriceName, $service) {
            if ($pkg->id !== $service->package_id && $pkg->stock != -1 && $pkg->stock <= 0) {
                return false;
            }

            $matchingPrice = $pkg->prices->first(function ($price) use ($currency, $currentPriceName) {
                if ($price->name !== $currentPriceName) return false;
                
                if (strtolower($price->type) === 'free') return true; 

                $rates = is_string($price->rates) ? json_decode($price->rates, true) : $price->rates;
                return isset($rates[$currency]) && ($rates[$currency]['enabled'] ?? false);
            });

            if (!$matchingPrice) return false;

            $pkg->target_price_model = $matchingPrice;

            return true;
        })->values();
    }

    /**
     * Resolve the target package with validated pricing and filtered scalable variants for the given service.
     *
     * @param  \App\Models\Service  $service
     * @param  int  $targetPackageId
     * @return \App\Models\Package|null
     */
    public function getStrictTargetPackage(Service $service, int $targetPackageId): ?Package
    {
        $currentPriceName = $service->packagePrice->name;
        $currency = $service->currency;

        $targetPackage = Package::where('status', 'visible')->find($targetPackageId);
        if (!$targetPackage) return null;

        $validPackagePrice = $targetPackage->prices->first(function ($p) use ($currentPriceName, $currency) {
            if ($p->name !== $currentPriceName) return false;
            
            if (strtolower($p->type) === 'free') return true;

            $rates = is_string($p->rates) ? json_decode($p->rates, true) : $p->rates;
            return isset($rates[$currency]) && ($rates[$currency]['enabled'] ?? false);
        });

        if (!$validPackagePrice) return null;
        $targetPackage->target_price_model = $validPackagePrice;

        $targetPackage->load(['variants' => function ($q) {
            $q->where('is_scalable', true)->where('status', 'visible')->orderBy('created_at', 'asc');
        }, 'variants.options.prices']);

        $targetPackage->variants->transform(function ($variant) use ($currentPriceName, $currency) {
            $filteredOptions = $variant->options->filter(function ($option) use ($currentPriceName, $currency) {
                $price = $option->prices->firstWhere('name', $currentPriceName);
                if (!$price) return false;

                if (strtolower($price->type) === 'free') return true;

                $rates = is_string($price->rates) ? json_decode($price->rates, true) : $price->rates;
                return isset($rates[$currency]) && ($rates[$currency]['enabled'] ?? false);
            })->map(function($option) use ($currentPriceName) {
                $option->target_price_model = $option->prices->firstWhere('name', $currentPriceName);
                return $option;
            })->values();

            $variant->setRelation('options', $filteredOptions);
            
            return $variant;
        });

        return $targetPackage;
    }

    /**
     * Calculate the prorated amount payable for scaling the service to the target package.
     *
     * @param  \App\Models\Service  $service
     * @param  \App\Models\Package  $targetPackage
     * @param  array  $selectedVariants
     * @return array
     *
     * @throws \Exception
     */
    public function calculateProrata(Service $service, Package $targetPackage, array $selectedVariants): array
    {
        $now = Carbon::now();
        $dueDate = $service->next_due_date ? Carbon::parse($service->next_due_date) : $now;

        if ($now->gte($dueDate)) {
            throw new Exception(__('client/services.scaling.service_overdue'));
        }

        $remainingDays = max(0, $now->diffInDays($dueDate));

        $interval = $service->billing_interval ?? 1;
        $period = $service->billing_period ?? 'monthly';
        $totalDaysInCycle = match($period) {
            'daily' => 1 * $interval,
            'weekly' => 7 * $interval,
            'monthly' => 30 * $interval,
            'yearly' => 365 * $interval,
            default => 30
        };

        $newTotalRecurring = $this->pricingService->calculateTotal(
            $targetPackage->target_price_model,
            $selectedVariants,
            $service->currency
        );

        $currentTotalRecurring = $service->price;
        $isDowngrade = $newTotalRecurring < $currentTotalRecurring;

        if ($isDowngrade) {
            $prorataAmount = 0;
        } else {
            $dailyRateNew = $newTotalRecurring / $totalDaysInCycle;
            $dailyRateOld = $currentTotalRecurring / $totalDaysInCycle;
            $prorataAmount = max(0, round(($dailyRateNew - $dailyRateOld) * $remainingDays, 2));
        }

        return [
            'currency' => $service->currency,
            'new_recurring' => $newTotalRecurring,
            'old_recurring' => $currentTotalRecurring,
            'remaining_days' => $remainingDays,
            'payable_amount' => $prorataAmount,
            'new_package_price_id' => $targetPackage->target_price_model->id,
            'is_downgrade' => $isDowngrade
        ];
    }

    /**
     * Execute the scaling order by creating a scaling record and generating a prorated invoice.
     *
     * @param  \App\Models\Service  $service
     * @param  \App\Models\Package  $targetPackage
     * @param  array  $calculation
     * @param  array  $selectedVariants
     * @return \App\Models\Invoice
     */
    public function executeOrder(Service $service, Package $targetPackage, array $calculation, array $selectedVariants): Invoice
    {
        return DB::transaction(function () use ($service, $targetPackage, $calculation, $selectedVariants) {
            
            $scaling = ServiceScaling::create([
                'service_id' => $service->id,
                'old_package_id' => $service->package_id,
                'new_package_id' => $targetPackage->id,
                'old_package_price_id' => $service->package_price_id,
                'new_package_price_id' => $calculation['new_package_price_id'],
                'variant_selections' => $selectedVariants,
                'currency' => $calculation['currency'],
                'old_price' => $calculation['old_recurring'],
                'new_price' => $calculation['new_recurring'],
                'payable_amount' => $calculation['payable_amount'],
                'prorata_days' => $calculation['remaining_days'],
                'status' => 'pending',
            ]);

            $invoice = Invoice::create([
                'user_id' => $service->user_id,
                'plugin_id' => null,
                'status' => 'unpaid',
                'currency' => $calculation['currency'],
                'subtotal' => $calculation['payable_amount'],
                'discount' => 0,
                'total' => $calculation['payable_amount'],
                'due_date' => now()->addDays(7),
            ]);

            $scaling->update(['invoice_id' => $invoice->id]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'description' => __('client/services.scaling.invoice_item_description', [
                    'old_package' => $service->package->name,
                    'new_package' => $targetPackage->name,
                    'days' => $calculation['remaining_days'],
                ]),
                'quantity' => 1,
                'unit_price' => $calculation['payable_amount'],
                'amount' => $calculation['payable_amount'],
            ]);

            return $invoice;
        });
    }

    /**
     * Determine whether the service is currently eligible to be scaled.
     *
     * @param  \App\Models\Service  $service
     * @return bool
     */
    public function canBeScaled(Service $service): bool
    {
        if ($service->status !== 'active') {
            return false;
        }

        $hasUpgradePath = $service->package->scalablePackages()
            ->where('packages.status', 'visible')
            ->exists();

        if ($hasUpgradePath) {
            return true;
        }

        if ($service->package->status === 'visible') {
            $hasScalableVariants = $service->package->variants()
                ->where('status', 'visible')
                ->where('is_scalable', true)
                ->exists();

            if ($hasScalableVariants) {
                return true;
            }
        }

        return false;
    }
}