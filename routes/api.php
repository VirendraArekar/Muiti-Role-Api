<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

//Auth routes
Route::post('/login', [AuthController::class,'login']);
Route::post('/register',  [AuthController::class,'register']);
Route::post('/forget-password',  [AuthController::class,'forgetPassword']);
Route::post('/reset-password',  [AuthController::class,'resetPassword']);

// Route for admin permissions
Route::prefix('admin')->group(function() {
	Route::post('/login',  [AuthController::class,'adminLogin']);
	Route::post('/register',  [AuthController::class,'adminRegister']);
});

// feature api
// Route::get('products', 'ProductController@index');
// Route::get('products/{products}', 'ProductController@show')->middleware(['auth:api', 'scope:get_user']);
// Route::post('product', 'ProductController@store')->middleware(['auth:api', 'scope:create_user']);
// Route::put('product/{product}', 'ProductController@update')->middleware(['auth:api', 'scope:edit_user']);
// Route::delete('product/{product}', 'ProductController@destroy')->middleware(['auth:api', 'scope:delete_user']);
