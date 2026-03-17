<?php

namespace App\Livewire\Admin\Services;

use App\Models\Currency;
use App\Models\Package;
use App\Models\Service;
use App\Services\Package\PricingService;
use App\Services\PluginManager;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ServiceEdit extends Component
{
    public Service $service;
    
    public string $service_currency = '';
    public string $package_id = '';
    public string $package_price_id = '';

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
     * Initialize the component with the given service, restoring
     * currency, package, and billing cycle fields from old input
     * or falling back to the service's current persisted values.
     *
     * @param  \App\Models\Service  $service
     * @return void
     */
    public function mount(Service $service)
    {
        $this->service = $service;
        
        $this->service_currency = old('service_currency', $service->currency ?? '');
        $this->package_id = old('package_id', $service->package_id ?? '');
        $this->package_price_id = old('package_price_id', $service->package_price_id ?? '');
    }

    /**
     * Retrieve all currencies ordered by code for the currency selector.
     * Persisted computed property; result is cached across Livewire requests.
     *
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Currency>
     */
    #[Computed(persist: true)]
    public function currencies()
    {
        return Currency::orderBy('code')->get();
    }

    /**
     * Retrieve all visible packages that have at least one available price
     * for the currently selected service currency.
     * Returns an empty collection if no currency is selected.
     *
     * @return \Illuminate\Support\Collection<\App\Models\Package>
     */
    #[Computed]
    public function availablePackages()
    {
        if (empty($this->service_currency)) return collect();

        return Package::with(['catalog:id,name', 'prices'])
            ->where('status', 'visible')
            ->get()
            ->filter(fn ($package) => $this->pricingService->getAvailablePackagePrices($package, $this->service_currency)->isNotEmpty());
    }

    /**
     * Resolve and return the full Package model for the currently selected package,
     * eager loading prices, visible variants with their option prices, and plugin relation.
     * Returns null if no package is selected.
     *
     * @return \App\Models\Package|null
     */
    #[Computed]
    public function selectedPackageModel()
    {
        if (empty($this->package_id)) return null;

        return Package::with([
            'prices',
            'variants' => fn($q) => $q->where('status', 'visible'),
            'variants.options.prices',
            'plugin'
        ])->find($this->package_id);
    }

    /**
     * Retrieve the available billing cycle prices for the selected package
     * filtered to the currently selected service currency.
     * Returns an empty collection if currency or package is not yet selected.
     *
     * @return \Illuminate\Support\Collection<\App\Models\PackagePrice>
     */
    #[Computed]
    public function availablePrices()
    {
        if (empty($this->service_currency) || !$this->selectedPackageModel) return collect();

        return $this->pricingService->getAvailablePackagePrices($this->selectedPackageModel, $this->service_currency);
    }

    /**
     * Retrieve the available variants for the selected package, filtered to options
     * that have a price matching the selected billing cycle name and currency.
     * Variants with no qualifying options after filtering are excluded.
     * Returns an empty collection if currency, billing cycle, or package is not yet selected.
     *
     * @return \Illuminate\Support\Collection<\App\Models\PackageVariant>
     */
    #[Computed]
    public function availableVariants()
    {
        if (empty($this->service_currency) || empty($this->package_price_id) || !$this->selectedPackageModel) {
            return collect();
        }

        $selectedPriceModel = $this->availablePrices->firstWhere('id', $this->package_price_id);
        if (!$selectedPriceModel) return collect();

        $variants = $this->pricingService->getAvailableVariants($this->selectedPackageModel, $this->service_currency);

        return $variants->map(function ($variant) use ($selectedPriceModel) {
            $filteredOptions = $variant->options->filter(fn ($option) => 
                $option->prices->contains('name', $selectedPriceModel->name)
            );
            $variant->setRelation('options', $filteredOptions);
            return $variant;
        })->filter(fn ($variant) => $variant->options->isNotEmpty());
    }

    /**
     * Resolve the dynamic checkout form schema from the selected package's
     * associated plugin instance. Returns an empty array if no package is selected,
     * the package has no plugin, or the plugin does not implement getCheckoutSchema().
     *
     * @return array
     */
    #[Computed]
    public function checkoutSchema()
    {
        if (!$this->selectedPackageModel || !$this->selectedPackageModel->plugin) return [];

        $instance = $this->pluginManager->bootInstance($this->selectedPackageModel->plugin);
        
        return ($instance && method_exists($instance, 'getCheckoutSchema')) 
            ? ($instance->getCheckoutSchema() ?: []) 
            : [];
    }

    /**
     * Render the service edit Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('admin::livewire.services.service-edit');
    }
}