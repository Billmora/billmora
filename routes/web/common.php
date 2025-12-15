<?php

use App\Http\Controllers;
use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/preference/update', [Controllers\PreferenceController::class, 'update'])->name('common.preference.update');