<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Controller;
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
Route::post('/login', [UserController::class, 'login']);
Route::post('/register',[UserController::class, 'register']);
Route::post('add-product', [UserController::class, 'addProduct']);
Route::get('get-products', [UserController::class, 'getProducts']);
Route::post('delete-products/{id}', [UserController::class, 'deleteProducts']);
Route::post('edit-products/{id}', [UserController::class, 'editProducts']);
Route::get('user_info', [UserController::class, 'getUsers']);
Route::post('delete-user/{id}',[UserController::class,'deleteUser']);
Route::post('edit-user/{id}',[UserController::class,'editUser']);

Route::group(['middleware' => 'auth:api'], function(){
Route::post('user-details', [UserController::class, 'userDetails']);
Route::post('user-logout', [UserController::class, 'logout']);
});
