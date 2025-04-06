<?php

use App\Http\Controllers\Portal;
use Illuminate\Support\Facades\Route;

Route::get('/', [Portal\IndexController::class, 'index'])->middleware('portal.check');

Route::group(['prefix' => '/preference'], function () {
    Route::post('/language', [Portal\PreferenceController::class, 'setLanguage'])->name('preference.language');
});