<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TelegramAuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Micromagicman\TelegramWebApp\Http\WebAppDataValidationMiddleware;

Route::get('/language', [LocaleController::class, 'index'])->name('locale.index');
Route::patch('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::post('/locale', [LocaleController::class, 'store'])->name('locale.store');

Route::get('/', static function () {
    return Inertia::render('Welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register/send-code', [RegisterController::class, 'sendCode'])->name('register.send-code');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login/send-code', [LoginController::class, 'sendCode'])->name('login.send-code');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::prefix('telegram')->withoutMiddleware([SetLocale::class])->group(function () {
    Route::get('/load', [TelegramAuthController::class, 'load'])->name('telegram.load');
    Route::get('/auth', [TelegramAuthController::class, 'auth'])
        ->middleware(WebAppDataValidationMiddleware::class)
        ->name('telegram.auth');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', static function () {
        return Inertia::render('Dashboard/Index');
    })->name('dashboard');
});
