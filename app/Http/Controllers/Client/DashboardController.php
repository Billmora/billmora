<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Locale;

class DashboardController extends Controller
{
  public function index()
  {
    return view('client::dashboard');
  }
}
