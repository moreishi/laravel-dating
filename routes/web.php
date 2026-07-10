<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profiles', [UserProfileController::class, 'index'])->name('profiles.index');
    Route::get('/profiles/{id}', [UserProfileController::class, 'show'])->name('profiles.show');
    Route::get('/profile/dating', [UserProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('/profile/dating', [UserProfileController::class, 'update'])->name('profiles.update')
        ->middleware('throttle:5,1');

    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::post('/conversations', [ConversationController::class, 'store'])->name('conversations.store')
        ->middleware('throttle:5,1');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');

    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store')
        ->middleware('throttle:10,1');
});

require __DIR__.'/auth.php';
