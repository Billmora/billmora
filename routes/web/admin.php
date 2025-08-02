<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin'], function () {
  Route::get('/', [Admin\DashboardController::class, 'index'])->name('admin.dashboard.index');
});