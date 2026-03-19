<?php

use App\Http\Controllers\Api\MentorController;
use Illuminate\Support\Facades\Route;

Route::get('/mentor/{login}', MentorController::class)->name('api.mentor.show');
