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

Route::get('user_info', [UserController::class, 'getUsers']);
Route::post('edit-user/{id}', [UserController::class, 'editUser']);
Route::post('edit-publish/{id}', [UserController::class, 'editPublished']);
Route::post('edit-grant/{id}', [UserController::class, 'editGrant']);
Route::delete('delete-user/{id}', [UserController::class, 'deleteUser']);
Route::post('add-product', [UserController::class, 'addProduct']);
Route::get('get-products/{galleryType}', [UserController::class, 'getProducts']);
Route::get('get_counts/', [UserController::class, 'getCounts']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/csv_file', [UserController::class, 'readCsv']);
Route::delete('delete-products/{id}', [UserController::class, 'deleteProducts']);
Route::post('edit-products/{id}', [UserController::class, 'editProducts']);
Route::get('get_users_statisticks', [UserController::class, 'get_users_statisticks']);
Route::post('forget-password', [UserController::class, 'forget_password']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('user-logout', [UserController::class, 'logout']);
    Route::post('user-details', [UserController::class, 'userDetails']);
});
