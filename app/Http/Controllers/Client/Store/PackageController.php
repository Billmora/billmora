<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Package;
use Illuminate\Http\Request;
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

        $package = Package::where('slug', $packageSlug)
            ->when(!Auth::user() || !Auth::user()->isAdmin(), function ($query) {
                $query->where('status', 'visible');
            })
            ->where('catalog_id', $catalog->id)
            ->with('prices')
            ->firstOrFail();

        if ($package->stock === 0) {
            return redirect()->route('client.store.catalog', ['catalog' => $catalog->slug])->with('error', __('client/store.stock_unavailable', ['item' => $package->name]));
        }

        $currencyCode = Session::get('currency');

        $prices = $package->prices->filter(function ($price) use ($currencyCode) {
            if ($price->type === 'free') {
                return true;
            }

            $rate = $price->rates[$currencyCode] ?? null;

            return $rate
                && ($rate['enabled'] ?? false) === true
                && ($rate['price'] ?? null) !== null;
        })->values();

        if ($prices->isEmpty()) {
            return redirect()->route('client.store.catalog', ['catalog' => $catalog->slug])->with('error', __('client/store.unavailable_currency'));
        }

        return view('client::store.catalog.package.show', compact('package', 'prices'));
    }
}
