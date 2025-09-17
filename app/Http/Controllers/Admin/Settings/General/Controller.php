<?php

namespace App\Http\Controllers\Admin\Settings\General;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

abstract class Controller extends BaseController
{

    /**
     * Create a new controller instance.
     *
     * Applies permission-based middleware for accessing general settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.general.view')->only('index');
        $this->middleware('permission:settings.general.update')->only('store');
    }
}
