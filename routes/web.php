<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\TrickController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\HandleSessionUser;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Home')->name('home');

Route::group(['middleware' => [HandleSessionUser::class]], function () {
    Route::get('lobby/{lobby}', [LobbyController::class, 'lobby'])->name('lobby');
    Route::get('game/{game}', [GameController::class, 'game'])->name('game');
});

Route::group(['prefix' => 'action'], function () {
    Route::post('user/create-user', [UserController::class, 'createUser'])->name('create-user');

    Route::group(['middleware' => [HandleSessionUser::class]], function () {
        Route::post('lobby/create-lobby', [LobbyController::class, 'createLobby'])->name('create-lobby');
        Route::post('lobby/join-lobby', [LobbyController::class, 'joinLobby'])->name('join-lobby');
        Route::post('game/start-game', [GameController::class, 'startGame'])->name('start-game');

        Route::post('round/perform-bet', [RoundController::class, 'performBet'])->name('perform-bet');
        Route::post('trick/play-card', [TrickController::class, 'playCard'])->name('play-card');
    });
});

