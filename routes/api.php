<?php

use App\Http\Controllers\Api\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/gateways/{plugin}/callback', [Gateway\CallbackController::class, 'handle'])->name('api.gateways.callback');