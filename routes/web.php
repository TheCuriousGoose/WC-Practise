<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LobbyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');


Route::resource('lobbies', LobbyController::class);
Route::post('lobbies/{lobby}/submit-user', [LobbyController::class, 'submitUser'])->name('lobbies.submit-user');
Route::post('lobbies/{lobby}/updates', [LobbyController::class, 'updates'])->name('lobbies.get-updates');

//Start game route
Route::post('lobbies/{lobby}/start', [LobbyController::class, 'startGame'])->name('lobbies.start-game');

// End turn route
Route::post('lobbies/{lobby}/end-turn', [LobbyController::class, 'endTurn'])->name('lobbies.end-turn');

// Game end get images
Route::post('lobbies/{lobby}/get-images', [LobbyController::class, 'getImages'])->name('lobbies.get-images');
