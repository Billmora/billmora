<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Tld;

class DomainController extends Controller
{
    /**
     * Display the domain search and pricing page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('client::store.domains.index');
    }
}
