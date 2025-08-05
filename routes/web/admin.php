<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin'], function () {
  Route::get('/', [Admin\DashboardController::class, 'index'])->name('admin.dashboard');

  // Global command-bar search endpoint (used by AlpineJS component in admin UI)
  Route::get('/quick-search', [Admin\QuickSearchController::class, 'search'])->name('admin.quick-search');
});