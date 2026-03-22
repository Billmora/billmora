<?php

use App\Http\Controllers;
use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/preference/update', [Controllers\PreferenceController::class, 'update'])->name('common.preference.update');

Route::get('/gateways/{plugin}/return', [Controllers\Api\Gateway\CallbackController::class, 'handleReturn'])->name('client.gateways.return');