<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

Route::group(
	[
		'middleware' => ['auth:sanctum'],
	],
	function () {
		Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

		Route::resource('survey', SurveyController::class);

		Route::get('/dashboard', [DashboardController::class, 'index']);
	}
);

Route::get('/survey-by-slug/{survey:slug}', [SurveyController::class, 'showPublic']);
Route::post('/survey/{survey}/answer', [SurveyController::class, 'storeAnswer']);

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
