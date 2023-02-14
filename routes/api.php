<?php

use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('register', [StudentController::class, 'postRegister']);
Route::post('login', [StudentController::class, 'postLogin']);
Route::post('update-profile', [StudentController::class, 'postUpdateProfile']);
Route::post('like', [StudentController::class, 'postLike']);
Route::get('get-list-friends', [StudentController::class, 'getListFriend']);
