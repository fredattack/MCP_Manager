<?php

use App\Http\Controllers\AdobeFetchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Adobe Fetch routes
    Route::post('adobe-fetch/execute', [AdobeFetchController::class, 'execute'])->name('adobe-fetch.execute');
    Route::get('adobe-fetch/logs', [AdobeFetchController::class, 'streamLogs'])->name('adobe-fetch.logs');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
