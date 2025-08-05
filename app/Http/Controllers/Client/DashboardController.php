<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    /**
     * Display the client dashboard homepage.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('client::index');
    }
}
