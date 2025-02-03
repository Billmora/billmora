<?php

use App\Http\Controllers\Client;
use Illuminate\Support\Facades\Route;

Route::get('/', [Client\PortalController::class, 'index']);
