<?php

use App\Http\Controllers\ChatboxController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\NotificationController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['Jwt'])->group(function () {

Route::post('/create/post',[PostController::class,'post']);
Route::put('/post/update/{id}',[PostController::class,'update']);
Route::delete('/post/delete/{id}',[PostController::class,'delete']);
Route::get('/get/post/{id}',[PostController::class,'get_post']);

});

//Route::get('/send_notification',[NotificationController::class,'send_notification']);





