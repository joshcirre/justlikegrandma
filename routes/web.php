<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('recipes', [RecipeController::class, 'store'])->name('recipes.store');
    Route::post('votes', [VoteController::class, 'store'])->name('votes.store');
    Route::get('archive', [ArchiveController::class, 'index'])->name('archive.index');
    Route::get('archive/{date}', [ArchiveController::class, 'show'])
        ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
        ->name('archive.show');
});

require __DIR__.'/settings.php';
