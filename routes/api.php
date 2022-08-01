<?php

use App\Http\Controllers\LobbyController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\HandleSessionUser;
use Illuminate\Support\Facades\Route;

// todo
Route::post('user/create-user', [UserController::class, 'createUser']);

Route::group(['middleware' => [HandleSessionUser::class]], function () {
    Route::post('lobby/create-lobby', [LobbyController::class, 'createLobby']);
});
