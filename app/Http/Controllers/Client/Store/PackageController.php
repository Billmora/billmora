<?php

namespace App\Http\Controllers\Client\Store;

use App\Facades\Currency as FacadesCurrency;
use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Package;
use App\Services\Package\PricingService;
use App\Services\PluginManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PackageController extends Controller
{

    /**
     * Display the specified package with pricing and variants for current currency.
     *
     * @param string $catalogSlug
     * @param string $packageSlug
     * @param \App\Services\Package\PricingService $pricingService
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $catalogSlug, string $packageSlug, PricingService $pricingService, PluginManager $pluginManager)
    {
        $catalog = Catalog::where('slug', $catalogSlug)->firstOrFail();
        $package = $this->getPackage($catalog, $packageSlug);
        $currencyCode = Session::get('currency');

        $prices = $pricingService->getAvailablePackagePrices($package, $currencyCode);

        if ($prices->isEmpty()) {
            return redirect()
                ->route('client.store.catalog', ['catalog' => $catalog->slug])
                ->with('error', __('client/store.unavailable_currency'));
        }

        $packagePricesPayload = $prices->map(
            fn($price) => $pricingService->mapPriceToPayload($price, $currencyCode)
        )->values()->toArray();

        $variants = $pricingService->getAvailableVariants($package, $currencyCode);
        $variantsPayload = $pricingService->buildVariantsPayload($variants);

        $checkoutSchema = [];
        if ($package->plugin) {
            $instance = $pluginManager->bootInstance($package->plugin);
            if ($instance && method_exists($instance, 'getCheckoutSchema')) {
                $checkoutSchema = $instance->getCheckoutSchema();
            }
        }

        return view('client::store.package.show', compact(
            'package',
            'prices',
            'packagePricesPayload',
            'variants',
            'variantsPayload',
            'checkoutSchema',
        ));
    }

    /**
     * Get package with visibility and stock validation.
     *
     * @param \App\Models\Catalog $catalog
     * @param string $slug
     * @return \App\Models\Package
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function getPackage(Catalog $catalog, string $slug): Package
    {
        $query = Package::where('slug', $slug)
            ->where('catalog_id', $catalog->id)
            ->with(['prices', 'variants.options.prices', 'catalog']);

        if (!Auth::check() || !Auth::user()->isAdmin()) {
            $query->where('status', 'visible');
        }

        $package = $query->firstOrFail();

        if ($package->stock === 0) {
            abort(redirect()
                ->route('client.store.catalog', $catalog->slug)
                ->with('error', __('client/store.stock_unavailable', ['item' => $package->name]))
            );
        }

        return $package;
    }
}
