<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Locale;

class PortalController extends Controller
{
  public function __construct()
    {
      $langsDirectory = File::directories(resource_path('lang'));

      $langs = collect($langsDirectory)->mapWithKeys(function ($path) {
        $locale = basename($path);
        return [$locale => Locale::getDisplayName($locale, app()->getLocale())];
      })->toArray();

      View::share('langs', $langs);
    }

    public function index()
    {
      if (Billmora::getGeneral('company_portal') == '0') {
        return redirect()->route('client.dashboard');
      }

      return view('portal::index');
    }
}
