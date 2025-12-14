<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Package;
use Illuminate\Http\Request;

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
        $package = Package::where('slug', $packageSlug)->firstOrFail();

        return view('client::store.catalog.package.show', compact('package'));
    }
}
