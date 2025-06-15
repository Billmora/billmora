<?php

namespace App\Http\Controllers\Client\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DetailController extends Controller
{
    public function index() {
        return view('client::account.detail');
    }
}
