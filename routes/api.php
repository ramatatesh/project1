<?php

use App\Http\Controllers\AdmainController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\NoteController;
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
Route::put('updateAccount',[UserController::class,'updateAccount']);
Route::post('deleteUser',[UserController::class,'deleteUser']);
Route::post('addTeacher',[TeacherController::class,'addTeacher']);
Route::post('addAdmain',[AdmainController::class,'addAdmain']);
Route::post('addEmployee',[EmployeeController::class,'addEmployee']);
Route::get('showEmployee',[EmployeeController::class,'showEmployee']);

Route::get('studentsByGrade/{grade}', [StudentController::class, 'studentsByGrade']);
Route::post('addNote', [NoteController::class, 'store'])->middleware('auth:sanctum');
Route::post('updateNote/{id}', [NoteController::class, 'update'])->middleware('auth:sanctum');
Route::delete('deleteNote/{id}', [NoteController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('allnoteStudent', [NoteController::class, 'allnoteStudent'])->middleware('auth:sanctum');
Route::post('getStudentsByGradeAndClassroom', [StudentController::class, 'getStudentsByGradeAndClassroom']);


