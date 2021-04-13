<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserController;

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
Route::get('email/verify/{id}', [UserController::class, 'verify'])->name('verification.verify');
Route::get('email/resend', [UserController::class, 'resend'])->name('verification.resend');

Route::middleware('auth:api')->group( function () {
    // Route::resource('profile', UserController::class);
});