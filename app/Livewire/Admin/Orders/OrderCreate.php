<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Coupon;
use App\Models\Package;
use App\Models\User;
use App\Services\Package\PricingService;
use App\Services\PluginManager;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OrderCreate extends Component
{
    public string $order_currency = '';
    public string $order_package = '';
    public string $order_package_billing = '';

    protected PricingService $pricingService;
    protected PluginManager $pluginManager;

    public function boot(PricingService $pricingService, PluginManager $pluginManager)
    {
        $this->pricingService = $pricingService;
        $this->pluginManager = $pluginManager;
    }

    public function mount()
    {
        $this->order_currency = old('order_currency', '');
        $this->order_package = old('order_package', '');
        $this->order_package_billing = old('order_package_billing', '');
    }

    public function updatedOrderCurrency()
    {
        $this->reset(['order_package', 'order_package_billing']);
    }

    public function updatedOrderPackage()
    {
        $this->reset('order_package_billing');
    }

    #[Computed(persist: true)]
    public function userOptions()
    {
        return User::select('id', 'first_name', 'last_name', 'email')
            ->get()
            ->map(fn ($user) => [
                'value' => $user->id,
                'title' => $user->fullname,
                'subtitle' => $user->email,
            ])->toArray();
    }

    #[Computed(persist: true)]
    public function couponOptions()
    {
        return Coupon::select('id', 'code')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->map(fn ($coupon) => [
                'value' => $coupon->code,
                'title' => $coupon->code,
            ])->toArray();
    }

    #[Computed]
    public function availablePackages()
    {
        if (empty($this->order_currency)) return collect();

        return Package::with(['catalog:id,name', 'prices'])
            ->get()
            ->filter(fn ($package) => $this->pricingService->getAvailablePackagePrices($package, $this->order_currency)->isNotEmpty());
    }

    #[Computed]
    public function selectedPackageModel()
    {
        if (empty($this->order_package)) return null;

        return Package::with([
            'prices',
            'variants' => fn($q) => $q->where('status', 'visible'),
            'variants.options.prices',
            'plugin'
        ])->find($this->order_package);
    }

    #[Computed]
    public function availablePrices()
    {
        if (empty($this->order_currency) || !$this->selectedPackageModel) return collect();

        return $this->pricingService->getAvailablePackagePrices($this->selectedPackageModel, $this->order_currency);
    }

    #[Computed]
    public function availableVariants()
    {
        if (empty($this->order_currency) || empty($this->order_package_billing) || !$this->selectedPackageModel) {
            return collect();
        }

        $selectedPriceModel = $this->availablePrices->firstWhere('id', $this->order_package_billing);
        if (!$selectedPriceModel) return collect();

        $variants = $this->pricingService->getAvailableVariants($this->selectedPackageModel, $this->order_currency);

        return $variants->map(function ($variant) use ($selectedPriceModel) {
            $filteredOptions = $variant->options->filter(fn ($option) => 
                $option->prices->contains('name', $selectedPriceModel->name)
            );
            $variant->setRelation('options', $filteredOptions);
            return $variant;
        })->filter(fn ($variant) => $variant->options->isNotEmpty());
    }

    #[Computed]
    public function checkoutSchema()
    {
        if (!$this->selectedPackageModel || !$this->selectedPackageModel->plugin) return [];

        $instance = $this->pluginManager->bootInstance($this->selectedPackageModel->plugin);
        
        return ($instance && method_exists($instance, 'getCheckoutSchema')) 
            ? ($instance->getCheckoutSchema() ?: []) 
            : [];
    }

    public function render()
    {
        return view('admin::livewire.orders.order-create');
    }
}