<?php

use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\RoleRequestController;
use App\Http\Controllers\API\UserController;
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
    // Route::post('logout', 'AuthController@logout');
    // Route::post('refresh', 'AuthController@refresh');
    // Route::post('me', 'AuthController@me');
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
});
