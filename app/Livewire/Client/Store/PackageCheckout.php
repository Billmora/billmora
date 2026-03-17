<?php

namespace App\Livewire\Client\Store;

use App\Models\Package;
use App\Models\PackagePrice;
use App\Services\Package\PricingService;
use App\Services\PluginManager;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Facades\Currency;

class PackageCheckout extends Component
{
    public Package $package;
    public string $currencyCode;

    public $selectedBillingId;
    public array $variantSelections = [];
    public array $sliderIndexes = [];

    protected PricingService $pricingService;
    protected PluginManager $pluginManager;

    /**
     * Inject required services via Livewire's boot lifecycle hook,
     * which runs on every request before mount.
     *
     * @param  \App\Services\Package\PricingService  $pricingService
     * @param  \App\Services\PluginManager   $pluginManager
     * @return void
     */
    public function boot(PricingService $pricingService, PluginManager $pluginManager)
    {
        $this->pricingService = $pricingService;
        $this->pluginManager = $pluginManager;
    }

    /**
     * Initialize the checkout component for the given package.
     * Restores the billing cycle and variant selections from old input,
     * merging both single and multi-select variant values. Falls back to
     * the first available price when no prior billing selection exists,
     * then validates all selections against the active billing cycle.
     *
     * @param  \App\Models\Package  $package
     * @return void
     */
    public function mount(Package $package)
    {
        $this->package = $package;
        $this->currencyCode = Session::get('currency');

        $this->selectedBillingId = old('price_id');
        
        $oldVariants = old('variants', []);
        $oldMultiVariants = old('variants_multi', []);
        
        foreach (array_replace($oldVariants, $oldMultiVariants) as $key => $val) {
            $this->variantSelections[$key] = $val;
        }

        if (!$this->selectedBillingId && !empty($this->availablePrices)) {
            $this->selectedBillingId = $this->availablePrices[0]['id'];
        }

        $this->validateSelections();
    }

    /**
     * Re-validate variant selections whenever the billing cycle changes
     * to ensure all current selections remain valid for the new cycle.
     *
     * @return void
     */
    public function updatedSelectedBillingId()
    {
        $this->validateSelections();
    }

    /**
     * React to a slider index change for a specific variant, resolve the
     * corresponding option from the available options list, and update
     * the variant selection to match the new slider position.
     *
     * @param  int|string  $value      The new slider index position
     * @param  int|string  $variantId  The ID of the slider variant being updated
     * @return void
     */
    public function updatedSliderIndexes($value, $variantId)
    {
        $variant = collect($this->availableVariants)->firstWhere('id', $variantId);
        if ($variant) {
            $options = array_values($this->getAvailableOptions($variant));
            if (isset($options[$value])) {
                $this->variantSelections[$variantId] = $options[$value]['id'];
            }
        }
    }

    /**
     * Validate and normalize all current variant selections against the options
     * available for the selected billing cycle. Resets out-of-scope selections
     * to the first available option and syncs slider indexes accordingly.
     * Checkbox selections are intersected with the available option IDs.
     *
     * @return void
     */
    private function validateSelections()
    {
        foreach ($this->availableVariants as $variant) {
            $availableOptions = array_values($this->getAvailableOptions($variant));
            $availableIds = array_column($availableOptions, 'id');
            
            if ($variant['type'] === 'checkbox') {
                $this->variantSelections[$variant['id']] = array_intersect(
                    $this->variantSelections[$variant['id']] ?? [], 
                    $availableIds
                );
            } else {
                if (!in_array($this->variantSelections[$variant['id']] ?? null, $availableIds)) {
                    $this->variantSelections[$variant['id']] = $availableIds[0] ?? null;
                    if ($variant['type'] === 'slider') {
                        $this->sliderIndexes[$variant['id']] = 0;
                    }
                } else {
                    if ($variant['type'] === 'slider') {
                        $index = array_search($this->variantSelections[$variant['id']], $availableIds);
                        $this->sliderIndexes[$variant['id']] = $index !== false ? $index : 0;
                    }
                }
            }
        }
    }

    /**
     * Retrieve the available billing cycle prices for the current package
     * and currency, mapped to a flat payload array for use in the component.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function availablePrices()
    {
        $prices = $this->pricingService->getAvailablePackagePrices($this->package, $this->currencyCode);
        return $prices->map(
            fn($price) => $this->pricingService->mapPriceToPayload($price, $this->currencyCode)
        )->values()->toArray();
    }

    /**
     * Retrieve the available variants for the current package and currency,
     * built into a structured payload array ready for rendering.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function availableVariants()
    {
        $variants = $this->pricingService->getAvailableVariants($this->package, $this->currencyCode);
        return $this->pricingService->buildVariantsPayload($variants);
    }

    /**
     * Resolve the dynamic checkout form schema from the package's associated
     * plugin instance. Returns an empty array if the package has no plugin
     * or the plugin does not implement getCheckoutSchema().
     *
     * @return array
     */
    #[Computed]
    public function checkoutSchema()
    {
        if (!$this->package->plugin) return [];

        $instance = $this->pluginManager->bootInstance($this->package->plugin);
        return ($instance && method_exists($instance, 'getCheckoutSchema')) 
            ? ($instance->getCheckoutSchema() ?: []) 
            : [];
    }

    /**
     * Calculate the full pricing summary for the currently selected billing cycle,
     * variant selections, and currency via PricingService.
     * Returns an empty array if no billing cycle is selected or the price record
     * cannot be resolved.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function pricingSummary()
    {
        if (!$this->selectedBillingId) return [];

        $packagePrice = PackagePrice::find($this->selectedBillingId);
        if (!$packagePrice) return [];

        return $this->pricingService->calculatePricing(
            $packagePrice,
            $this->variantSelections,
            null, 
            $this->currencyCode
        );
    }

    /**
     * Filter a variant's options to only those with a price defined
     * under the currently selected billing cycle name.
     *
     * @param  array  $variant  A single variant entry from $availableVariants
     * @return array            Filtered options keyed by their original index
     */
    public function getAvailableOptions($variant)
    {
        if (!$this->selectedBillingId) return [];
        
        $cycleName = collect($this->availablePrices)->firstWhere('id', $this->selectedBillingId)['name'] ?? '';

        return array_filter($variant['options'], function ($option) use ($cycleName) {
            return isset($option['prices_by_name'][$cycleName]);
        });
    }

    /**
     * Format a human-readable price label for a variant option based on the
     * active billing cycle and session currency. Returns 'Free' when both
     * the recurring price and setup fee are zero, and appends a setup fee
     * suffix when applicable.
     *
     * @param  array   $option  A single option entry from a variant's options list
     * @return string           Formatted price string, or empty string if unavailable
     */
    public function formatOptionPrice($option)
    {
        if (!$this->selectedBillingId) return '';
        $cycleName = collect($this->availablePrices)->firstWhere('id', $this->selectedBillingId)['name'] ?? '';
        
        $priceData = $option['prices_by_name'][$cycleName] ?? null;
        if (!$priceData) return '';

        $price = (float) $priceData['price'];
        $setupFee = (float) $priceData['setup_fee'];

        if ($price == 0 && $setupFee == 0) return __('billing.cycles.free');
        
        $priceStr = $price == 0 ? __('billing.cycles.free') : Currency::format($price, $this->currencyCode);
        
        if ($setupFee > 0) {
            return $priceStr . ' + ' . Currency::format($setupFee, $this->currencyCode) . ' ' . __('client/store.package.setup_fee');
        }

        return $priceStr;
    }

    /**
     * Render the package checkout Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('client::livewire.store.package-checkout');
    }
}