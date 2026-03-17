<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Package;
use App\Services\Package\PricingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PackageController extends Controller
{

    public function show(string $catalogSlug, string $packageSlug, PricingService $pricingService)
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

        return view('client::store.package.show', compact('package'));
    }

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
