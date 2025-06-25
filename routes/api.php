<?php

use App\Http\Controllers\GeminiController;
use App\Http\Controllers\MettingController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\TaskController;
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
    Route::delete("deleteprojet/{id}", [ProjetController::class, "destroy"]);
    Route::put("updateprojet/{id}", [ProjetController::class, "update"]);

    // UserController
    Route::get("getprofileuser", [UserController::class, "GetProfileUser"]);
    Route::put("updateUser", [UserController::class, "updateProfile"]);

    // TaskController
    Route::get("getTasksByUser/{id}", [TaskController::class, "getTasksByUser"]);
    Route::post("storeTask/{id}", [TaskController::class, "storeTask"]);
    Route::delete("deletetask/{id}", [TaskController::class, "destroy"]);
    Route::put("updatetask/{id}", [TaskController::class, "update"]);

    // Gemeni Controller
    Route::post('ask-gemini-2', [GeminiController::class, 'StoreTheTaskAutomatique']);
    // Meeting Controller
    Route::post('create-meeting', [MettingController::class, 'create']);


});
