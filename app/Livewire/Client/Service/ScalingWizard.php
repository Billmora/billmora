<?php

namespace App\Livewire\Client\Service;

use App\Models\Service;
use App\Services\Service\ScalingService;
use Livewire\Component;
use App\Facades\Currency;

class ScalingWizard extends Component
{
    public Service $service;
    
    public int $step = 1;
    public $selectedPackageId = null;
    public array $variantSelections = [];
    public array $calculation = [];

    protected ScalingService $scalingService;

    /**
     * Inject the ScalingService dependency via Livewire's boot lifecycle hook.
     * Called automatically on every request before mount and other lifecycle methods.
     *
     * @param  \App\Services\Service\ScalingService  $scalingService
     * @return void
     */
    public function boot(ScalingService $scalingService)
    {
        $this->scalingService = $scalingService;
    }

    /**
     * Initialize the scaling wizard with the given service.
     * Validates that the service is eligible for scaling via ScalingService;
     * redirects back with an error message if validation fails.
     * Sets the current package as the default selected package.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function mount(Service $service)
    {
        $this->service = $service;

        try {
            $this->scalingService->validateRequest($service);
        } catch (\Exception $e) {
            return redirect()->route('client.services.show', ['service' => $service->service_number])
                ->with('error', $e->getMessage());
        }

        $this->selectedPackageId = $service->package_id;
    }

    /**
     * Retrieve all packages eligible as scaling candidates for the current service.
     * Uses strict candidate rules from ScalingService (same catalog, same provisioning, etc.).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailablePackagesProperty()
    {
        return $this->scalingService->getStrictCandidates($this->service);
    }

    /**
     * Resolve and return the currently selected target package with strict compatibility checks.
     * Returns null if no package is selected or if the selected package fails strict validation.
     *
     * @return \App\Models\Package|null
     */
    public function getTargetPackageProperty()
    {
        if (!$this->selectedPackageId) return null;
        return $this->scalingService->getStrictTargetPackage($this->service, $this->selectedPackageId);
    }

    /**
     * Validate the selected package and advance the wizard to step 2.
     * Pre-populates variant selections from the service's existing selections,
     * falling back to each variant's first available option, then triggers recalculation.
     *
     * @return void
     */
    public function goToStep2()
    {
        $this->validate(['selectedPackageId' => 'required|integer']);

        $target = $this->targetPackage;
        if (!$target) {
            $this->addError('general', __('client/services.scaling.invalid_package'));
            return;
        }

        foreach ($target->variants as $variant) {
            $this->variantSelections[$variant->id] = $this->service->variant_selections[$variant->id] 
                ?? $variant->options->first()->id 
                ?? null;
        }

        $this->step = 2;
        $this->recalculate();
    }

    /**
     * Navigate back to step 1 and clear all validation errors.
     *
     * @return void
     */
    public function goToStep1()
    {
        $this->step = 1;
        $this->resetErrorBag();
    }

    /**
     * React to any variant selection change and trigger a prorata recalculation.
     *
     * @return void
     */
    public function updatedVariantSelections()
    {
        $this->recalculate();
    }

    /**
     * Recalculate the prorata cost for scaling to the target package with the
     * current variant selections. Stores the result in $calculation.
     * Adds a general error if the calculation throws an exception.
     *
     * @return void
     */
    public function recalculate()
    {
        try {
            $this->resetErrorBag('general');
            $this->calculation = $this->scalingService->calculateProrata(
                $this->service, 
                $this->targetPackage, 
                $this->variantSelections
            );
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    /**
     * Format a human-readable price label for a variant option relative to the
     * service's active currency. Handles free pricing, zero-price edge cases,
     * and appends the setup fee suffix when applicable.
     *
     * @param  \App\Models\PackageVariantOption  $option  Option instance with a resolved target_price_model
     * @return string                                      Formatted price string, or empty string if unavailable
     */
    public function formatOptionPrice($option): string
    {
        $priceModel = $option->target_price_model ?? null;
        if (!$priceModel) return '';

        $currency = $this->service->currency;
        $isFree = strtolower($priceModel->type) === 'free';

        if ($isFree) {
            return __('billing.cycles.free');
        }

        $rates = is_string($priceModel->rates) ? json_decode($priceModel->rates, true) : $priceModel->rates;
        $price = (float) ($rates[$currency]['price'] ?? 0);
        $setupFee = (float) ($rates[$currency]['setup_fee'] ?? 0);

        if ($price == 0 && $setupFee == 0) {
            return __('billing.cycles.free');
        }

        $priceStr = $price == 0 ? __('billing.cycles.free') : Currency::format($price, $currency);

        if ($setupFee > 0) {
            return $priceStr . ' + ' . Currency::format($setupFee, $currency) . ' ' . __('billing.cycles.setup_fee');
        }

        return $priceStr;
    }

    /**
     * Submit the scaling order after verifying meaningful changes exist.
     * Guards against no-op submissions when the same package and identical
     * variant selections are chosen. On success, executes the scaling order via
     * ScalingService, generates an invoice, and redirects to the invoice detail
     * page with an upgrade or downgrade success message.
     *
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function submit()
    {
        $target = $this->targetPackage;

        if ($target->id === $this->service->package_id) {
            $currentVariants = $this->service->variant_selections ?? [];
            $cleanVariants = $this->variantSelections;
            
            ksort($currentVariants);
            ksort($cleanVariants);
            
            if (json_encode($currentVariants) === json_encode($cleanVariants)) {
                $this->addError('general', __('client/services.scaling.no_variant_changes'));
                return;
            }
        }

        try {
            $scalingService = $this->scalingService;
            $calc = $scalingService->calculateProrata($this->service, $target, $this->variantSelections);
            $invoice = $scalingService->executeOrder($this->service, $target, $calc, $this->variantSelections);

            $msg = $calc['is_downgrade']
                ? __('client/services.scaling.downgrade_success')
                : __('client/services.scaling.upgrade_success');

            return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])
                ->with('success', $msg);

        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    /**
     * Render the scaling wizard Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('client::livewire.service.scaling-wizard');
    }
}