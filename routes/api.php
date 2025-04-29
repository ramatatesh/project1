<?php

use App\Http\Controllers\AdmainController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login',[UserController::class,'login']);
Route::post('logout',[UserController::class,'logout'])->middleware('auth:sanctum');
Route::post('forgetPassword',[UserController::class,'forgetPassword']);
Route::post('resetPassword',[UserController::class,'resetPassword']);
Route::post('addStudent',[StudentController::class,'addStudent']);
Route::put('updateAccount',[UserController::class,'updateAccount'])->middleware('auth:sanctum');
Route::post('addTeacher',[TeacherController::class,'addTeacher']);
Route::post('addAdmain',[AdmainController::class,'addAdmain']);
