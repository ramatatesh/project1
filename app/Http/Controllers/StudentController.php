<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Models\Student;
use App\Models\User;
use App\Models\Note;
use App\Models\Grade;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /*public function addStudent(StoreStudentRequest $request)
{
    $validatedData = $request->validated();

    $user = User::create([
        'username' => $validatedData['username'],
        'father_name' => $validatedData['father_name'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
        'phone' => $validatedData['phone'],
        'address' => $validatedData['address'],
        'role' => 'student'
    ]);


    $grade = Grade::firstOrCreate(['name' => $validatedData['grade']]);


    $classrooms = Classroom::where('grade_id', $grade->id)->get();

    if ($classrooms->count() === 0) {
        foreach (['A', 'B', 'C', 'D'] as $name) {
            Classroom::create([
                'grade_id' => $grade->id,
                'name' => $name,
            ]);
        }

        $classrooms = Classroom::where('grade_id', $grade->id)->get();
    }

    $availableClassrooms = $classrooms->filter(function ($classroom) {
        return $classroom->students()->count() < 35;
    });

    if ($availableClassrooms->isEmpty()) {
        return response()->json([
            'message' => 'لا توجد شُعب متاحة حالياً لهذا الصف. الرجاء إنشاء شعبة جديدة.',
        ], 400);
    }


    $targetClassroom = $availableClassrooms->sortBy(function ($classroom) {
        return $classroom->students()->count();
    })->first();


    $student = Student::create([
        'user_id' => $user->id,
        'mother_name' => $validatedData['mother_name'],
        'birth_date' => $validatedData['birth_date'],
        'gender' => $validatedData['gender'],
        'grade' => $validatedData['grade'],
        'classroom_id' => $targetClassroom->id,
        'section' => $targetClassroom->name,
    ]);

    return response()->json([
        'message' => 'تم تسجيل الطالب وتوزيعه على شعبة',
        'User' => $user,
        'Student' => $student,
    ], 201);
}*/

public function addStudent(StoreStudentRequest $request)
{
    $validatedData = $request->validated();

    $grade = Grade::where('name', $validatedData['grade'])->first();

    if (!$grade) {
        return response()->json([
            'message' => 'الصف الذي أدخلته غير موجود في النظام.',
        ], 400);
    }

    $user = User::create([
        'username' => $validatedData['username'],
        'father_name' => $validatedData['father_name'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
        'phone' => $validatedData['phone'],
        'address' => $validatedData['address'],
        'role' => 'student'
    ]);


    $grade = Grade::firstOrCreate(['name' => $validatedData['grade']]);

    $classrooms = Classroom::where('grade_id', $grade->id)->get();

    if ($classrooms->count() === 0) {
        foreach (['A', 'B', 'C', 'D'] as $name) {
            Classroom::create([
                'grade_id' => $grade->id,
                'name' => $name,
            ]);
        }

        $classrooms = Classroom::where('grade_id', $grade->id)->get();
    }


    $availableClassrooms = $classrooms->filter(function ($classroom) {
        return $classroom->students()->count() < 35;
    });

    if ($availableClassrooms->isEmpty()) {
        return response()->json([
            'message' => 'لا توجد شُعب متاحة حالياً لهذا الصف. الرجاء إنشاء شعبة جديدة.',
        ], 400);
    }


    $targetClassroom = $availableClassrooms->sortBy(function ($classroom) {
        return $classroom->students()->count();
    })->first();


    $student = Student::create([
        'user_id' => $user->id,
        'mother_name' => $validatedData['mother_name'],
        'birth_date' => $validatedData['birth_date'],
        'gender' => $validatedData['gender'],
        'grade_id' => $grade->id,
        'classroom_id' => $targetClassroom->id,
        'section' => $targetClassroom->name,
    ]);

    return response()->json([
        'message' => 'تم تسجيل الطالب وتوزيعه على شعبة',
        'User' => $user,
        'Student' => $student,
    ], 201);
}


//________________________________________________________________________________________
public function getStudentsByGradeAndClassroom(Request $request)
{
    $validated = $request->validate([
        'grade' => 'required|string',
        'section' => 'required|string',
    ]);


    $grade = Grade::where('name', $validated['grade'])->first();


    $classroom = Classroom::where('grade_id', $grade->id)
        ->where('name', $validated['section'])
        ->first();

    $students = Student::where('classroom_id', $classroom->id)
        ->with('user')
        ->get();

    if ($students->isEmpty()) {
        return response()->json([
            'message' => 'لا يوجد طلاب في هذا الصف وهذه الشعبة.',
        ], 404);
    }

    return response()->json([
        'grade' => $grade->name,
        'section' => $classroom->name,
        'students' => $students,
    ]);
}



//________________________________________________________________________________________



}

