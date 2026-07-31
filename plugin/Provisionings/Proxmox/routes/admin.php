<?php

use Illuminate\Support\Facades\Route;
use Plugins\Provisionings\Proxmox\Http\Controllers\Admin;

Route::middleware('permission:provisionings.proxmox.manage')->group(function () {
    Route::get('/', [Admin\VmOverviewController::class, 'index'])->name('index');
    Route::post('{vmid}/start', [Admin\VmOverviewController::class, 'start'])->name('vms.start');
    Route::post('{vmid}/stop', [Admin\VmOverviewController::class, 'stop'])->name('vms.stop');

    Route::resource('ip-pools', Admin\IpPoolController::class)->except(['show']);
    
    Route::prefix('ip-pools/{pool}')->name('ip-pools.')->group(function () {
        Route::get('ips', [Admin\IpAddressController::class, 'index'])->name('ips.index');
        Route::get('ips/create', [Admin\IpAddressController::class, 'create'])->name('ips.create');
        Route::post('ips', [Admin\IpAddressController::class, 'store'])->name('ips.store');
        Route::delete('ips/{ip}', [Admin\IpAddressController::class, 'destroy'])->name('ips.destroy');
    });
    Route::get('nodes', [Admin\NodeController::class, 'index'])->name('nodes.index');
});
