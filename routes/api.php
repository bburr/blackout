<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\TrickController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\HandleSessionUser;
use Illuminate\Support\Facades\Route;

// todo middleware to wrap non-GET calls in transaction?
// User
Route::post('user/create-user', [UserController::class, 'createUser']);

Route::group(['middleware' => [HandleSessionUser::class]], function () {
    // Lobby
    Route::post('lobby/create-lobby', [LobbyController::class, 'createLobby']);
    Route::post('lobby/join-lobby', [LobbyController::class, 'joinLobby']);

    // Game
    Route::post('game/start-game', [GameController::class, 'startGame']);

    // Round
    Route::post('round/perform-bet', [RoundController::class, 'performBet']);

    // Trick
    Route::post('trick/play-card', [TrickController::class, 'playCard']);

    // Player
    Route::get('player/get-hand', [PlayerController::class, 'getHand']);
});

// todo add authentication
Route::group(['prefix' => 'admin'], function () {
    Route::post('user/create-other-user', [UserController::class, 'createOtherUser']);
    Route::post('lobby/add-user-to-lobby', [LobbyController::class, 'addUserToLobby']);
    Route::post('round/perform-bet-as-user', [RoundController::class, 'performBetAsUser']);
    Route::post('trick/play-card-as-user', [TrickController::class, 'playCardAsUser']);
    Route::get('player/get-hand-as-user', [PlayerController::class, 'getHandAsUser']);
});
