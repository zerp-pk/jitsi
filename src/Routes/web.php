<?php

use Zerp\Jitsi\Http\Controllers\JitsiController;

use Illuminate\Support\Facades\Route;
use Zerp\Jitsi\Http\Controllers\JitsiSettingsController;

Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:Jitsi'])->group(function () {
    Route::post('/jitsi/settings', [JitsiSettingsController::class, 'update'])->name('jitsi.settings.update');

    Route::prefix('jitsi-meetings')->name('jitsi.jitsi-meetings.')->group(function () {
        Route::get('/', [JitsiController::class, 'index'])->name('index');
        Route::post('/', [JitsiController::class, 'store'])->name('store');

        Route::put('/{jitsimeeting}', [JitsiController::class, 'update'])->name('update');
        Route::delete('/{jitsimeeting}', [JitsiController::class, 'destroy'])->name('destroy');
        Route::patch('/{jitsimeeting}/status', [JitsiController::class, 'updateStatus'])->name('update-status');
    });
});