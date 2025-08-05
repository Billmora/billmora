<?php

use App\Http\Controllers\Client;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('client.dashboard');