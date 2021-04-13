<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\API\ProfileController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('invite', [UserController::class, 'invite']);
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('verify', [UserController::class, 'verify']);
Route::get('email-resend', [UserController::class, 'resend']);

Route::middleware('auth:api')->group( function () {
    Route::get('profile/get', [ProfileController::class, 'show']);
    Route::post('profile/update', [ProfileController::class, 'update']);
});