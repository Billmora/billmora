<?php

use Illuminate\Support\Facades\Route;
use Plugins\Modules\Announcement\Http\Controllers\Admin\AnnouncementController;

Route::middleware('permission:modules.announcement.manage')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
    Route::post('/', [AnnouncementController::class, 'store'])->name('store');
    Route::get('/{post}/edit', [AnnouncementController::class, 'edit'])->name('edit');
    Route::put('/{post}', [AnnouncementController::class, 'update'])->name('update');
    Route::delete('/{post}', [AnnouncementController::class, 'destroy'])->name('destroy');
});
