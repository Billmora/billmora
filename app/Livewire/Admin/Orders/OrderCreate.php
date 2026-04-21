<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Package;
use App\Models\Tld;
use App\Models\User;
use App\Services\Package\PricingService;
use App\Services\PluginManager;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OrderCreate extends Component
{
    public string $order_currency = '';

    /** @var array<string, array{package_id: string, billing_id: string, quantity: int}> */
    public array $packageItems = [];

    /** @var array<string, array{type: string, domain: string, tld_id: string, years: int, epp_code: string}> */
    public array $domainItems = [];

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

        // Restore old package items or start empty
        $oldPkgs = old('package_items', []);
        if (!empty($oldPkgs)) {
            foreach ($oldPkgs as $pkg) {
                $this->packageItems[uniqid()] = [
                    'package_id' => $pkg['package_id'] ?? '',
                    'billing_id' => $pkg['billing_id'] ?? '',
                    'quantity' => $pkg['quantity'] ?? 1,
                ];
            }
        }

        // Restore old domain items or start empty
        $oldDoms = old('domain_items', []);
        if (!empty($oldDoms)) {
            foreach ($oldDoms as $dom) {
                $this->domainItems[uniqid()] = [
                    'type' => $dom['type'] ?? 'register',
                    'domain' => $dom['domain'] ?? '',
                    'tld_id' => $dom['tld_id'] ?? '',
                    'years' => $dom['years'] ?? 1,
                    'epp_code' => $dom['epp_code'] ?? '',
                ];
            }
        }
    }

    /**
     * Reset all item selections when currency changes.
     */
    public function updatedOrderCurrency()
    {
        foreach ($this->packageItems as $i => $item) {
            $this->packageItems[$i]['package_id'] = '';
            $this->packageItems[$i]['billing_id'] = '';
        }
        foreach ($this->domainItems as $i => $item) {
            $this->domainItems[$i]['tld_id'] = '';
        }
    }

    /**
     * Reset billing when package changes for a specific item.
     */
    public function updatedPackageItems($value, $key)
    {
        // key format: "uuid.package_id" or "uuid.billing_id"
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'package_id') {
            $this->packageItems[$parts[0]]['billing_id'] = '';
        }
    }

    // ─── Repeater Actions ──────────────────────────────────

    public function addPackageItem(): void
    {
        $this->packageItems[uniqid()] = [
            'package_id' => '',
            'billing_id' => '',
            'quantity' => 1,
        ];
    }

    public function removePackageItem(string $index): void
    {
        unset($this->packageItems[$index]);
    }

    public function addDomainItem(): void
    {
        $this->domainItems[uniqid()] = [
            'type' => 'register',
            'domain' => '',
            'tld_id' => '',
            'years' => 1,
            'epp_code' => '',
        ];
    }

    public function removeDomainItem(string $index): void
    {
        unset($this->domainItems[$index]);
    }

    // ─── Shared Computed Properties ────────────────────────

    #[Computed(persist: true)]
    public function currencies()
    {
        return Currency::orderBy('code')->get();
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

    // ─── Package Item Helpers ──────────────────────────────

    /**
     * Get available packages for a specific package item index.
     */
    public function getAvailablePackagesFor(string $index)
    {
        if (empty($this->order_currency)) return collect();

        return Package::with(['catalog:id,name', 'prices'])
            ->get()
            ->filter(fn ($package) => $this->pricingService->getAvailablePackagePrices($package, $this->order_currency)->isNotEmpty());
    }

    /**
     * Get the resolved Package model for a specific item.
     */
    public function getSelectedPackageFor(string $index): ?Package
    {
        $pkgId = $this->packageItems[$index]['package_id'] ?? '';
        if (empty($pkgId)) return null;

        return Package::with([
            'prices',
            'variants' => fn($q) => $q->where('status', 'visible'),
            'variants.options.prices',
            'plugin'
        ])->find($pkgId);
    }

    /**
     * Get available billing cycle prices for a specific package item.
     */
    public function getAvailablePricesFor(string $index)
    {
        if (empty($this->order_currency)) return collect();

        $package = $this->getSelectedPackageFor($index);
        if (!$package) return collect();

        return $this->pricingService->getAvailablePackagePrices($package, $this->order_currency);
    }

    /**
     * Get available variants for a specific package item, filtered by billing cycle.
     */
    public function getAvailableVariantsFor(string $index)
    {
        $billingId = $this->packageItems[$index]['billing_id'] ?? '';
        if (empty($this->order_currency) || empty($billingId)) return collect();

        $package = $this->getSelectedPackageFor($index);
        if (!$package) return collect();

        $prices = $this->pricingService->getAvailablePackagePrices($package, $this->order_currency);
        $selectedPrice = $prices->firstWhere('id', $billingId);
        if (!$selectedPrice) return collect();

        $variants = $this->pricingService->getAvailableVariants($package, $this->order_currency);

        return $variants->map(function ($variant) use ($selectedPrice) {
            $filteredOptions = $variant->options->filter(fn ($option) =>
                $option->prices->contains('name', $selectedPrice->name)
            );
            $variant->setRelation('options', $filteredOptions);
            return $variant;
        })->filter(fn ($variant) => $variant->options->isNotEmpty());
    }

    /**
     * Get checkout schema for a specific package item's plugin.
     */
    public function getCheckoutSchemaFor(string $index): array
    {
        $package = $this->getSelectedPackageFor($index);
        if (!$package || !$package->plugin) return [];

        $instance = $this->pluginManager->bootInstance($package->plugin);

        return ($instance && method_exists($instance, 'getCheckoutSchema'))
            ? ($instance->getCheckoutSchema() ?: [])
            : [];
    }

    // ─── Domain Item Helpers ───────────────────────────────

    /**
     * Get available TLDs that have prices in the selected currency.
     */
    #[Computed]
    public function availableTlds()
    {
        if (empty($this->order_currency)) return collect();

        return Tld::with(['prices' => fn($q) => $q->where('currency', $this->order_currency)])
            ->whereHas('prices', fn($q) => $q->where('currency', $this->order_currency))
            ->orderBy('tld')
            ->get();
    }

    /**
     * Get calculated domain price for a specific domain item.
     */
    public function getDomainPriceFor(string $index): ?float
    {
        $item = $this->domainItems[$index] ?? null;
        if (!$item || empty($item['tld_id']) || empty($this->order_currency)) return null;

        $tld = $this->availableTlds->firstWhere('id', $item['tld_id']);
        if (!$tld) return null;

        $tldPrice = $tld->prices->first();
        if (!$tldPrice) return null;

        $unitPrice = match ($item['type']) {
            'transfer' => (float) $tldPrice->transfer_price,
            default => (float) $tldPrice->register_price,
        };

        return $unitPrice * ($item['years'] ?? 1);
    }

    // ─── Render ────────────────────────────────────────────

    public function render()
    {
        return view('admin::livewire.orders.order-create');
    }
}