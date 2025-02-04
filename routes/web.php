<?php

use App\Http\Controllers\Portal;
use Illuminate\Support\Facades\Route;

Route::get('/', [Portal\IndexController::class, 'index']);
