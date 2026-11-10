<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PaymentSimulationController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PartnerDirectoryController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PartnerRegisterController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Pages publiques
Route::get('/partenaires', [PartnerDirectoryController::class, 'index'])->name('partners');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [RegisterController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->name('login.store');

    Route::get('/devenir-partenaire', [PartnerRegisterController::class, 'create'])->name('partners.register');
    Route::post('/devenir-partenaire', [PartnerRegisterController::class, 'store'])->name('partners.register.store');
});

Route::get('/verification', [OtpController::class, 'show'])->name('otp.show');
Route::post('/verification', [OtpController::class, 'verify'])->name('otp.verify');
Route::post('/verification/renvoyer', [OtpController::class, 'resend'])->name('otp.resend');

Route::post('/deconnexion', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Webhook des opérateurs : public, sans CSRF (la signature fait foi), limité en fréquence
Route::post('webhooks/paiement/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->withoutMiddleware([PreventRequestForgery::class])
    ->middleware('throttle:120,1')
    ->name('webhooks.payment');

// Faux opérateur (développement uniquement : 404 en production)
Route::middleware('auth')->prefix('paiement-simulation')->name('payments.simulation.')->group(function () {
    Route::get('{transaction}', [PaymentSimulationController::class, 'show'])->name('show');
    Route::post('{transaction}', [PaymentSimulationController::class, 'complete'])->name('complete');
});