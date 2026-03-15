<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use Illuminate\Http\Request;

class TermsController extends Controller
{

    /**
     * Display the terms of service page with visible catalogs and their available packages.
     *
     * @return \Illuminate\View\View
     */
    public function service()
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

        return view('portal::terms.service', compact('catalogs'));
    }

    /**
     * Display the terms and conditions page with visible catalogs and their available packages.
     *
     * @return \Illuminate\View\View
     */
    public function condition()
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

        return view('portal::terms.condition', compact('catalogs'));
    }

    /**
     * Display the privacy policy page with visible catalogs and their available packages.
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
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

        return view('portal::terms.privacy', compact('catalogs'));
    }
}
