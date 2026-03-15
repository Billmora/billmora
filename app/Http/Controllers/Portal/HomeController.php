<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $catalogs = Catalog::where('status', 'visible')
            ->with(['packages' => function ($query) {
                $query->where('status', 'visible')
                      ->with('prices');
            }])
            ->withCount(['packages' => function ($query) {
                $query->where('status', 'visible');
            }])
            ->get();

        return view('portal::index', compact('catalogs'));
    }
}
