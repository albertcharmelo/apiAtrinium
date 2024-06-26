<?php

use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\RoleRequestController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ActivityTypeController;
use App\Http\Controllers\API\ConvertCurrencyController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/* --------------------------------------- AUTHENTICATION --------------------------------------- */
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'store']);
    Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('user', [UserController::class, 'user'])->middleware('auth:sanctum');
});

/* -------------------------------------------- ROLES ------------------------------------------- */
Route::group(['prefix' => 'role'], function () {
    Route::post('request', [RoleRequestController::class, 'store'])->middleware('auth:sanctum');
    Route::put('request/{roleRequest}', [RoleRequestController::class, 'update'])->middleware('auth:sanctum');
});

/* ------------------------------------------ COMPANIES ----------------------------------------- */
Route::group(['prefix' => 'company'], function () {
    Route::get('/', [CompanyController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/', [CompanyController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/{company}', [CompanyController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/{company}', [CompanyController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{company}', [CompanyController::class, 'destroy'])->middleware('auth:sanctum');
    Route::post('toogle-activity-type', [CompanyController::class, 'toogleActivityType'])->middleware('auth:sanctum');
});

/* ------------------------------------------ ACTIVITY TYPES ----------------------------------------- */
Route::group(['prefix' => 'activity-type'], function () {
    Route::get('/', [ActivityTypeController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/', [ActivityTypeController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/{activityType}', [ActivityTypeController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/{activityType}', [ActivityTypeController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{activityType}', [ActivityTypeController::class, 'destroy'])->middleware('auth:sanctum');
});

/* -------------------------------------- CONVERT CURRENCY -------------------------------------- */
Route::group(['prefix' => 'currency'], function () {
    Route::post('convert', [ConvertCurrencyController::class, 'convert']);
    Route::get('update-historical', [ConvertCurrencyController::class, 'updateHistorical']);
});
