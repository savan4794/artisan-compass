<?php

use Illuminate\Support\Facades\Route;
use SavanRathod\ArtisanCompass\Http\Controllers\ArtisanController;

Route::prefix('artisan-compass')->middleware(['web'])->group(function () {
    Route::get('/', [ArtisanController::class, 'index']);
    Route::post('/run', [ArtisanController::class, 'run']);
});
