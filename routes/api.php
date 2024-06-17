<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\CarsController;
use App\Http\Controllers\V1\CarsOwnerController;
use App\Http\Controllers\V1\ServicesController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\WorkersController;

// Users
Route::post('/login',[UserController::class, 'login']);
Route::get('/users-all',[UserController::class, 'index']);
Route::post('/register',[UserController::class, 'register']);
Route::delete('/delete-user/{user}',[UserController::class, 'destroy']);
Route::put('update-user/{user}',[UserController::class, 'update']);
Route::post('/filter-users',[UserController::class, 'especial']);


Route::get('/workers-all',[WorkersController::class,'index']);
Route::post('/workers-register',[WorkersController::class,'register']);
Route::put('/update-worker',[WorkersController::class,'update']);
Route::delete('/delete-worker/{id}',[WorkersController::class, 'destroy']);
Route::post('/filter-workers',[WorkersController::class, 'especial']);
Route::get('/worker/{id}',[WorkersController::class,'show']);


Route::get('/owners-all',[CarsOwnerController::class,'index']);
Route::post('/owners-register',[CarsOwnerController::class,'register']);
Route::delete('/delete-owner/{id}',[CarsOwnerController::class, 'destroy']);
Route::put('/update-owner',[CarsOwnerController::class,'update']);
Route::get('/all-owner',[CarsOwnerController::class,'show']);
Route::get('/cars-all/{id}',[CarsOwnerController::class,'show']);
Route::post('/filter-owners',[CarsOwnerController::class, 'especial']);

Route::post('/cars-brand',[CarsController::class,'brand']);
Route::get('/cars-all',[CarsController::class,'index']);
Route::post('/cars-register',[CarsController::class,'register']);
Route::post('/cars-filter',[CarsController::class,'especial']);
Route::put('/cars-update',[CarsController::class,'update']);
Route::delete('/cars-delete/{id}',[CarsController::class,'destroy']);





Route::delete('/service-delete/{id}',[ServicesController::class,'destroy']);
Route::post('/service-register',[ServicesController::class,'register']);
Route::put('/service-update',[ServicesController::class,'update']);
Route::get('/all-services',[ServicesController::class,'index']);
Route::post('/filter-services',[ServicesController::class,'especial']);