<?php

namespace App\Http\Controllers\Client\Store;

use App\Facades\Currency as FacadesCurrency;
use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PackageController extends Controller
{

    /**
     * Display the specified package detail page for a given catalog.
     *
     * @param  string  $catalogSlug  The slug identifier of the catalog
     * @param  string  $packageSlug  The slug identifier of the package
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($catalogSlug, $packageSlug)
    {
        $catalog = Catalog::where('slug', $catalogSlug)->firstOrFail();

        $package = $this->getPackage($catalog, $packageSlug);

        $currencyCode = Session::get('currency');

        $prices = $this->getAvailablePackagePrices($package);

        if ($prices->isEmpty()) {
            return redirect()
                ->route('client.store.catalog', ['catalog' => $catalog->slug])
                ->with('error', __('client/store.unavailable_currency'));
        }

        $packagePricesPayload = $prices->map(function ($p) use ($currencyCode) {
            return $this->mapRateToPayload($p, $currencyCode);
        })->values()->toArray();

        $variants = $this->getAvailableVariants($package);
        $variantsPayload = $this->buildVariantsPayload($variants);

        return view('client::store.catalog.package.show', compact(
            'package',
            'prices',
            'packagePricesPayload',
            'variants',
            'variantsPayload',
        ));
    }

    /**
     * Retrieve a single package from a catalog by slug.
     *
     * @param  \App\Models\Catalog  $catalog
     * @param  string  $slug
     * @return \App\Models\Package
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function getPackage(Catalog $catalog, string $slug): Package
    {
        $query = Package::where('slug', $slug)
            ->where('catalog_id', $catalog->id)
            ->with('prices');

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

    /**
     * Get available prices for a package based on active currency.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Support\Collection<int, \App\Models\PackagePrice>
     */
    private function getAvailablePackagePrices(Package $package)
    {
        $currencyCode = session('currency');

        return $package->prices
            ->filter(function ($price) use ($currencyCode) {
                if ($price->type === 'free') {
                    return true;
                }

                $rate = $price->rates[$currencyCode] ?? null;

                return $rate
                    && ($rate['enabled'] ?? false)
                    && ($rate['price'] ?? null) !== null;
            })
            ->values();
    }

    /**
     * Retrieve visible variants and filter their options pricing based on the active currency.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Support\Collection<int, \App\Models\Variant>
     */
    private function getAvailableVariants(Package $package)
    {
        $currencyCode = session('currency');

        $package->load([
            'variants' => function ($query) {
                $query->where('status', 'visible');
            },
            'variants.options.prices',
        ]);

        return $package->variants->map(function ($variant) use ($currencyCode) {
            $variant->options = $variant->options->map(function ($option) use ($currencyCode) {
                $filtered = $option->prices->filter(function ($price) use ($currencyCode) {
                    if ($price->type === 'free') {
                        return true;
                    }

                    $rate = $price->rates[$currencyCode] ?? null;

                    return $rate
                        && ($rate['enabled'] ?? false)
                        && ($rate['price'] ?? null) !== null;
                })->values();

                $option->prices_by_name = $filtered->mapWithKeys(function ($price) use ($currencyCode) {
                    return [
                        $price->name => $this->mapRateToPayload($price, $currencyCode)
                    ];
                });

                $option->prices = $filtered;

                return $option;
            })->values();

            return $variant;
        })->values();
    }

    /**
     * Convert a price rate into a frontend-ready payload.
     *
     * @param  mixed   $price
     * @param  string  $currencyCode
     * @return array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     price: float|int,
     *     setup_fee: float|int,
     *     total: float|int,
     *     price_f: string,
     *     setup_fee_f: string,
     *     total_f: string
     * }
     */
    private function mapRateToPayload($price, string $currencyCode): array
    {
        $isFree = $price->type === 'free';

        $priceValue = $isFree ? 0 : ($price->rates[$currencyCode]['price'] ?? 0);
        $setupFee   = $isFree ? 0 : ($price->rates[$currencyCode]['setup_fee'] ?? 0);
        $total      = $priceValue + $setupFee;

        return [
            'id' => $price->id,
            'name' => $price->name,
            'type' => $price->type,

            'price' => $priceValue,
            'setup_fee' => $setupFee,
            'total' => $total,

            'price_f' => $isFree ? 'Free' : FacadesCurrency::format($priceValue),
            'setup_fee_f' => $isFree ? 'Free' : FacadesCurrency::format($setupFee),
            'total_f' => $isFree ? 'Free' : FacadesCurrency::format($total),
        ];
    }

    /**
     * Build a normalized variants payload for frontend consumption.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Variant>  $variants
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     options: array<int, array{
     *         id: int,
     *         name: string,
     *         value: string,
     *         p: array<string, array>
     *     }>
     * }>
     */
    private function buildVariantsPayload($variants): array
    {
        return $variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'type' => $variant->type,
                'options' => $variant->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                        'value' => $option->value,
                        // compact price labels by cycle name
                        'p' => $option->prices_by_name ?? [],
                    ];
                })->values(),
            ];
        })->values()->toArray();
    }
}
