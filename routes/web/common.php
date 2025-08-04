<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{lang}', [Controllers\LanguageController::class, 'update'])->name('common.language.update');