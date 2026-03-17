<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/language', [LocaleController::class, 'index'])->name('locale.index');
Route::patch('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::post('/locale', [LocaleController::class, 'store'])->name('locale.store');

Route::get('/', static function () {
    return Inertia::render('Welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', static function () {
        return Inertia::render('Dashboard/Index');
    })->name('dashboard');
});
