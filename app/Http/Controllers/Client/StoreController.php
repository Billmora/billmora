<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use Illuminate\Http\Request;

class StoreController extends Controller
{

    /**
     * Display a list of visible catalogs for the store.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $catalogs = Catalog::select('id', 'name', 'slug', 'description', 'icon', 'status')
            ->where('status', 'visible')
            ->get();

        return view('client::store.index', compact('catalogs'));
    }
}
