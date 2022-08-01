<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\HandleSessionUser;
use Illuminate\Support\Facades\Route;

// User
Route::post('user/create-user', [UserController::class, 'createUser']);

Route::group(['middleware' => [HandleSessionUser::class]], function () {
    // Lobby
    Route::post('lobby/create-lobby', [LobbyController::class, 'createLobby']);
    Route::post('lobby/join-lobby', [LobbyController::class, 'joinLobby']);

    // Game
    Route::post('game/start-game', [GameController::class, 'startGame']);
});

Route::group(['prefix' => 'admin'], function () {
    Route::post('user/create-other-user', [UserController::class, 'createOtherUser']);
    Route::post('lobby/add-user-to-lobby', [LobbyController::class, 'addUserToLobby']);
});
