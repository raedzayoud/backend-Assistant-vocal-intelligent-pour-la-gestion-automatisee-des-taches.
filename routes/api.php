<?php

use App\Http\Controllers\GeminiController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('ask-gemini', [GeminiController::class, 'askGemini']);

//Authentication
Route::post('register', [UserController::class, 'register']);
Route::get('login', [UserController::class, 'login']);
Route::post("logout", [UserController::class, "logout"])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    // ProjetController
    Route::get("getprojetsbyuser", [ProjetController::class, "getProjetsByUser"]);
    Route::post("storeprojet", [ProjetController::class, "storeProjet"]);
    Route::delete("deleteprojet", [ProjetController::class, "destroy"]);

    //



});
