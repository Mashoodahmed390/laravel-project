<?php

use App\Http\Controllers\FriendController;
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

Route::middleware(['Jwt'])->group(function () {

Route::post('/add/friend',[FriendController::class,'add_friend']);
Route::delete('/delete/friend',[FriendController::class,'remove_friend']);

});

