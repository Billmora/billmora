<?php

use Illuminate\Support\Facades\Route;
use Plugins\Modules\Announcement\Http\Controllers\Client\AnnouncementController;

Route::get('/', [AnnouncementController::class, 'index'])->name('index');
Route::get('/{post:slug}', [AnnouncementController::class, 'show'])->name('show');
