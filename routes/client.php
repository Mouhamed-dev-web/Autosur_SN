<?php

use App\Http\Controllers\Client\SosController;
use App\Http\Controllers\Client\InsuranceController;
use App\Http\Controllers\Client\TechnicalVisitController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\DocumentController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\VehicleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\PaymentController;
use App\Http\Controllers\Client\RatingController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');

Route::resource('vehicules', VehicleController::class)
    ->parameters(['vehicules' => 'vehicle'])
    ->names('vehicles');

Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
Route::get('documents/ajouter', [DocumentController::class, 'create'])->name('documents.create');
Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
Route::get('documents/{document}/telechargement', [DocumentController::class, 'download'])->name('documents.download');
Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

Route::get('alertes', [NotificationController::class, 'index'])->name('alerts.index');
Route::post('alertes/lues', [NotificationController::class, 'markAllRead'])->name('alerts.read');
Route::put('alertes/preferences', [NotificationController::class, 'updatePreferences'])->name('alerts.preferences');

Route::get('visites-techniques', [TechnicalVisitController::class, 'index'])->name('visits.index');
Route::get('visites-techniques/reserver', [TechnicalVisitController::class, 'create'])->name('visits.create');
Route::post('visites-techniques', [TechnicalVisitController::class, 'store'])->name('visits.store');
Route::post('visites-techniques/{visit}/annuler', [TechnicalVisitController::class, 'cancel'])->name('visits.cancel');

Route::get('assurance', [InsuranceController::class, 'index'])->name('insurance.index');
Route::get('assurance/devis', [InsuranceController::class, 'create'])->name('insurance.create');
Route::post('assurance/devis', [InsuranceController::class, 'store'])->name('insurance.store');
Route::get('assurance/devis/{quote}', [InsuranceController::class, 'show'])->name('insurance.show');
Route::post('assurance/devis/{quote}/souscrire/{offer}', [InsuranceController::class, 'subscribe'])->name('insurance.subscribe');

Route::get('sos', [SosController::class, 'index'])->name('sos.index');
Route::get('sos/nouvelle', [SosController::class, 'create'])->name('sos.create');
Route::post('sos', [SosController::class, 'store'])->name('sos.store');
Route::get('sos/{sosRequest}', [SosController::class, 'show'])->name('sos.show');
Route::get('sos/{sosRequest}/statut', [SosController::class, 'status'])->name('sos.status');
Route::post('sos/{sosRequest}/annuler', [SosController::class, 'cancel'])->name('sos.cancel');

Route::post('visites-techniques/{visit}/payer', [PaymentController::class, 'pay'])->name('visits.pay');
Route::get('paiements', [PaymentController::class, 'index'])->name('payments.index');
Route::get('paiements/{transaction}/retour', [PaymentController::class, 'result'])->name('payments.result');

Route::post('visites-techniques/{visit}/noter', [RatingController::class, 'visit'])->name('visits.rate');
Route::post('sos/{sosRequest}/noter', [RatingController::class, 'sos'])->name('sos.rate');