<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UserGuideController;
use App\Http\Controllers\Api\MpesaCallbackController;


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

Route::prefix('user-guide')->group(function () {
    Route::get('/', [UserGuideController::class, 'index']);
    Route::get('/search', [UserGuideController::class, 'search']);
    Route::get('/{slug}', [UserGuideController::class, 'show']);
});

Route::post(
    '/mpesa/stk/callback',
    [MpesaCallbackController::class, 'handle']
)->name('api.mpesa.stk.callback');
