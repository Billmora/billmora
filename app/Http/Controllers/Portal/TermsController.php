<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use Billmora;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    /**
     * Applies permission-based middleware for accessing service scalings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Billmora::getGeneral('term_tos')) {
                abort(404);
            }

            return $next($request);
        })->only(['service']);

        $this->middleware(function ($request, $next) {
            if (!Billmora::getGeneral('term_toc')) {
                abort(404);
            }

            return $next($request);
        })->only(['condition']);

        $this->middleware(function ($request, $next) {
            if (!Billmora::getGeneral('term_privacy')) {
                abort(404);
            }

            return $next($request);
        })->only(['privacy']);
    }

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
