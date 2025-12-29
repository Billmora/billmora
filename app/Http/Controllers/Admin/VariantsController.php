<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VariantsController extends Controller
{

    /**
     * Display a listing of variants with optional search filter.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing optional 'search' query.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::variants.index');
    }
}
