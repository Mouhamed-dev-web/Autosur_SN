<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Web\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [RegisterController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->name('login.store');
});

Route::get('/verification', [OtpController::class, 'show'])->name('otp.show');
Route::post('/verification', [OtpController::class, 'verify'])->name('otp.verify');
Route::post('/verification/renvoyer', [OtpController::class, 'resend'])->name('otp.resend');

Route::post('/deconnexion', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');