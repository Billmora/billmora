<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Package;
use Illuminate\Http\Request;
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
            ->where('catalog_id', $catalog->id)
            ->firstOrFail();

        $currencyCode = Session::get('currency');
        $price = $package->primaryPrice;

        $rate = $price->rates[$currencyCode] ?? null;

        if ($price->type !== 'free' && (! $rate || ($rate['enabled'] ?? false) !== true || ($rate['price'] ?? null) === null)) {
            return back()->with('error', __('client/store.unavailable_currency'));
        }

        return view('client::store.catalog.package.show', compact('package'));
    }
}
