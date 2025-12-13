<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use Illuminate\Http\Request;

class CatalogController extends Controller
{

    /**
     * Display all packages belonging to a specific catalog slug.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $catalogSlug  The slug identifying the catalog
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function index(Request $request, $catalogSlug)
    {
        $catalog = Catalog::where('slug', $catalogSlug)->firstOrFail();

        $packages = $catalog->packages()
            ->with('prices')
            ->get();

        foreach ($packages as $package) {
            $package->primaryPrice = $package->prices
                ->sortBy('id')
                ->first();
        }

        $catalogs = Catalog::select('id', 'name', 'slug', 'description', 'icon', 'status')
            ->where('status', 'visible')
            ->get();

        return view('client::store.catalog.index', compact('packages', 'catalogs'));
    }
}
