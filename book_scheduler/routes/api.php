<?php

use App\Http\Controllers\requestHandler;
use App\Http\Controllers\UserRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post("/wakey", [UserRequestController::class, "addWhatsappKey"]);
Route::get("/pdf", [RequestHandler::class, "getPdfTitle"]);
Route::post("/createJob", [UserRequestController::class, "createJobDB"]);
Route::post("/create", [UserRequestController::class, "createUser"]);
Route::middleware("auth:sanctum")->get("/user", function (Request $request) {
    return $request->user();
});
