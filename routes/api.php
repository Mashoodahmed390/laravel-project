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

Route::post('/signup',[UserController::class,'signup']);
Route::post('/login',[UserController::class,'login']);
Route::get('/verifyEmail/{email}',[UserController::class,'verify']);

Route::middleware(['Jwt'])->group(function () {

Route::post('/post',[PostController::class,'post']);
Route::get('/post/update/{id}',[PostController::class,'update']);
Route::get('/post/delete/{id}',[PostController::class,'delete']);
Route::get('/get/post/{id}',[PostController::class,'get_post']);

Route::get('/comment',[CommentController::class,'comment']);
Route::get('/comment/update',[CommentController::class,'update']);
Route::delete('/comment/delete',[CommentController::class,'delete']);
Route::get('/comment/post/view',[CommentController::class,'post_comment_display']);

Route::post('/add/friend',[FriendController::class,'add_friend']);
Route::delete('/delete/friend',[FriendController::class,'remove_friend']);

Route::post('/sendmessage',[ChatboxController::class,'send_message']);

});

//Route::get('/send_notification',[NotificationController::class,'send_notification']);





