<?php

use App\Http\Controllers\AdmainController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\WeeklyScheduleController;
use App\Http\Controllers\ClassroomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login',[UserController::class,'login']);
Route::post('logout',[UserController::class,'logout'])->middleware('auth:sanctum');
Route::post('forgetPassword',[UserController::class,'forgetPassword']);
Route::post('userCheckCode',[UserController::class,'userCheckCode']);
Route::post('resetPassword',[UserController::class,'resetPassword']);
Route::post('addStudent',[StudentController::class,'addStudent']);
Route::get('getGrades', [GradeController::class, 'getGrades']);
Route::post('updateStudent/{userId}',[StudentController::class,'updateStudent']);
Route::delete('destroyStudent/{userId}',[StudentController::class,'destroyStudent']);
Route::post('showStudent',[StudentController::class,'showStudent'])->middleware('auth:sanctum');;
Route::put('updateAccount',[UserController::class,'updateAccount']);
Route::post('deleteUser',[UserController::class,'deleteUser']);
Route::post('addTeacher',[TeacherController::class,'addTeacher']);
Route::post('updateTeacher/{userId}',[TeacherController::class,'updateTeacher']);
Route::delete('destroyTeacher/{userId}',[TeacherController::class,'destroyTeacher']);
Route::post('addAdmain',[AdmainController::class,'addAdmain']);
Route::post('updateAdmain/{userId}',[AdmainController::class,'updateAdmain']);
Route::post('addEmployee',[EmployeeController::class,'addEmployee']);
Route::post('updateEmployee/{userId}',[EmployeeController::class,'updateEmployee']);
Route::get('showEmployee',[EmployeeController::class,'showEmployee']);

Route::get('studentsByGrade/{grade}', [StudentController::class, 'studentsByGrade']);
Route::post('addNote', [NoteController::class, 'storeNote']);
Route::post('updateNote/{id}', [NoteController::class, 'update']);
Route::delete('deleteNote/{id}', [NoteController::class, 'destroy']);
Route::get('allnoteStudent', [NoteController::class, 'allnoteStudent'])->middleware('auth:sanctum');
Route::post('getStudentsByGradeAndClassroom', [StudentController::class, 'getStudentsByGradeAndClassroom']);
Route::post('storeWeeklySchedule', [WeeklyScheduleController::class, 'storeWeeklySchedule']);
Route::get('getWeeklySchedule', [WeeklyScheduleController::class, 'getWeeklySchedule'])->middleware('auth:sanctum');
Route::post('getClassroomsByGrade', [ClassroomController::class, 'getClassroomsByGrade']);

