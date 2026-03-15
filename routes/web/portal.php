<?php

use App\Http\Controllers;
use App\Http\Controllers\Portal;
use Illuminate\Support\Facades\Route;

Route::get('/', [Portal\HomeController::class, 'index'])->name('portal.home');