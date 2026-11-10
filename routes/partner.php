<?php

use App\Http\Controllers\Partner\DashboardController;
use App\Http\Controllers\Partner\InsuranceController;
use App\Http\Controllers\Partner\ProfileController;
use App\Http\Controllers\Partner\SosController;
use App\Http\Controllers\Partner\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');

// Dépanneur
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

// Garage
Route::middleware('role:garage')->prefix('rendez-vous')->name('visits.')->group(function () {
    Route::get('/', [VisitController::class, 'index'])->name('index');
    Route::post('{visit}/confirmer', [VisitController::class, 'confirm'])->name('confirm');
    Route::post('{visit}/refuser', [VisitController::class, 'reject'])->name('reject');
    Route::post('{visit}/terminer', [VisitController::class, 'complete'])->name('complete');
});

// Assureur
Route::middleware('role:assurance')->prefix('prospects')->name('insurance.')->group(function () {
    Route::get('/', [InsuranceController::class, 'index'])->name('index');
    Route::post('{subscription}/activer', [InsuranceController::class, 'activate'])->name('activate');
    Route::post('{subscription}/refuser', [InsuranceController::class, 'decline'])->name('decline');
});