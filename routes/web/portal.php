<?php

use App\Http\Controllers;
use App\Http\Controllers\Portal;
use Illuminate\Support\Facades\Route;

Route::get('/', [Portal\HomeController::class, 'index'])->name('portal.home');

Route::get('/terms-of-service', [Portal\TermsController::class, 'service'])->name('portal.terms.service');
Route::get('/terms-of-condition', [Portal\TermsController::class, 'condition'])->name('portal.terms.condition');
Route::get('/privacy-policy', [Portal\TermsController::class, 'privacy'])->name('portal.terms.privacy');