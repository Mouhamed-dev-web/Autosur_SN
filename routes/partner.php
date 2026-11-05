<?php

use App\Http\Controllers\Partner\DashboardController;
use App\Http\Controllers\Partner\SosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('role:depanneur')->prefix('sos')->name('sos.')->group(function () {
    Route::get('/', [SosController::class, 'index'])->name('index');
    Route::get('disponibles', [SosController::class, 'available'])->name('available');
    Route::post('disponibilite', [SosController::class, 'toggle'])->name('toggle');

    Route::get('{sosRequest}', [SosController::class, 'show'])->name('show');
    Route::post('{sosRequest}/accepter', [SosController::class, 'accept'])->name('accept');
    Route::post('{sosRequest}/refuser', [SosController::class, 'decline'])->name('decline');
    Route::post('{sosRequest}/arrive', [SosController::class, 'arrive'])->name('arrive');
    Route::post('{sosRequest}/terminer', [SosController::class, 'complete'])->name('complete');
});