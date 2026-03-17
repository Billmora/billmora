<?php

namespace App\Livewire\Client\Service;

use App\Models\Service;
use App\Services\Service\ScalingService;
use Livewire\Component;
use Livewire\Attributes\Computed;
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
     * Initialize the scaling wizard for the given service.
     * Validates that the service is eligible for scaling, then restores
     * prior form state from old input. If the user is returning from a failed
     * submission with a package already selected, skip directly to step 2
     * and recalculate the prorated amount.
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

        $this->selectedPackageId = old('package_id', $service->package_id);
        $this->variantSelections = old('variants', []);

        if (old('package_id') && session()->has('errors')) {
            $this->step = 2;
            $this->recalculate();
        }
    }

    /**
     * Retrieve all upgrade/downgrade candidate packages available
     * for the current service based on strict scaling rules.
     *
     * @return \Illuminate\Support\Collection
     */
    #[Computed]
    public function availablePackages()
    {
        return $this->scalingService->getStrictCandidates($this->service);
    }

    /**
     * Resolve the target Package model for the currently selected package ID,
     * validated against the strict scaling rules for the current service.
     * Returns null if no package is selected or the selection is invalid.
     *
     * @return \App\Models\Package|null
     */
    #[Computed]
    public function targetPackage()
    {
        if (!$this->selectedPackageId) return null;
        return $this->scalingService->getStrictTargetPackage($this->service, $this->selectedPackageId);
    }

    /**
     * Validate the selected package and advance to step 2 of the wizard.
     * Pre-populates variant selections from the service's existing variant
     * selections, falling back to each variant's first available option.
     * Triggers a prorated cost recalculation after advancing.
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

        if (empty($this->variantSelections)) {
            foreach ($target->variants as $variant) {
                $this->variantSelections[$variant->id] = $this->service->variant_selections[$variant->id] 
                    ?? $variant->options->first()->id 
                    ?? null;
            }
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
     * React to variant selection changes and trigger a prorated cost recalculation.
     *
     * @return void
     */
    public function updatedVariantSelections()
    {
        $this->recalculate();
    }

    /**
     * Recalculate the prorated cost for scaling to the target package
     * with the current variant selections. Stores the result in $calculation
     * or adds a general validation error if the calculation fails.
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
     * Format a human-readable price label for a variant option based on
     * the service's active currency. Resolves the price and setup fee from
     * the option's target price model rates, returning 'Free' when both are zero.
     * Appends a setup fee suffix when applicable.
     *
     * @param  \App\Models\VariantOption  $option  Option with an eager-loaded target_price_model
     * @return string                               Formatted price string, or empty string if unavailable
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
     * Render the scaling wizard Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('client::livewire.service.scaling-wizard');
    }
}