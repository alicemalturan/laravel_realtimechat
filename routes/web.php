<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/rooms', [ChatController::class, 'createRoom'])->middleware('throttle:chat-send')->name('rooms.store');
    Route::get('/rooms/{room}/messages', [ChatController::class, 'listMessages'])->name('rooms.messages');
    Route::post('/rooms/{room}/messages', [ChatController::class, 'sendMessage'])->middleware('throttle:chat-send')->name('rooms.messages.store');
    Route::post('/rooms/{room}/typing', [ChatController::class, 'typing'])->middleware('throttle:chat-typing')->name('rooms.typing');
    Route::post('/rooms/{room}/presence', [ChatController::class, 'presence'])->middleware('throttle:chat-presence')->name('rooms.presence');

    Route::post('/messages/{message}/reactions', [ChatController::class, 'addReaction'])->middleware('throttle:chat-send')->name('messages.reactions');
    Route::post('/messages/{message}/read', [ChatController::class, 'markAsRead'])->middleware('throttle:chat-presence')->name('messages.read');
});
