<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuditsController extends Controller
{

    /**
     * Display the admin audits shortcut page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::audits.index');
    }
}
