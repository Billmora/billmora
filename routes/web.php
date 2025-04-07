<?php

use App\Http\Controllers\Portal;
use Illuminate\Support\Facades\Route;

Route::get('/', [Portal\PortalController::class, 'index'])->name('portal.index');
Route::post('/preference', [Portal\PreferenceController::class, 'update'])->name('preference.update');