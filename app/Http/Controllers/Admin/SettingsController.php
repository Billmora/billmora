<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{

    /**
     * Display the admin settings overview page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings');
    }
}
