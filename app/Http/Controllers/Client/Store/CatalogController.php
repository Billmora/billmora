<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $catalog = Catalog::where('slug', $catalogSlug)
            ->when(!Auth::user() || !Auth::user()->isAdmin(), function ($query) {
                $query->where('status', 'visible');
            })
            ->firstOrFail();

        $packages = $catalog->packages()
            ->with(['prices', 'catalog'])
            ->where('status', 'visible')
            ->get();

        $catalogs = Catalog::select('id', 'name', 'slug', 'description', 'icon', 'status')
            ->where('status', 'visible')
            ->get();

        return view('client::store.catalog.index', compact('packages', 'catalogs', 'catalog'));
    }
}
