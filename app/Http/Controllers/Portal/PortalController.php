<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;

class PortalController extends Controller
{
  public function index()
  {
    if (Billmora::getGeneral('company_portal') == false) {
      return redirect()->route('client.dashboard');
    }

    return view('portal::index');
  }
}
