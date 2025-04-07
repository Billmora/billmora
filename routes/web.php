<?php

use App\Http\Controllers\Portal;
use App\Http\Controllers\Client;
use Illuminate\Support\Facades\Route;

Route::get('/', [Portal\PortalController::class, 'index'])->name('portal.index');
Route::post('/preference', [Portal\PreferenceController::class, 'update'])->name('preference.update');

Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('client.dashboard');