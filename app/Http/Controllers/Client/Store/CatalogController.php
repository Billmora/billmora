<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{

    /**
     * Display all visible packages within the specified catalog for the store page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Catalog  $catalog
     * @return \Illuminate\View\View
     */
    public function index(Request $request, Catalog $catalog)
    {
        $catalog->when(!Auth::user() || !Auth::user()->isAdmin(), function ($query) {
                $query->where('status', 'visible');
            })
            ->firstOrFail();

        $packages = $catalog->packages()
            ->with(['prices', 'catalog'])
            ->where('status', 'visible')
            ->get();

        $catalogs = Catalog::select('id', 'name', 'slug', 'description', 'icon', 'status')
            ->when(!Auth::user() || !Auth::user()->isAdmin(), function ($query) {
                $query->where('status', 'visible');
            })
            ->get();

        return view('client::store.catalog.index', compact('packages', 'catalogs', 'catalog'));
    }
}
