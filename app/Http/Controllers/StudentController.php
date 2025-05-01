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
    public function addStudent(StoreStudentRequest $request)
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
}




//________________________________________________________________________________________
public function getStudentsByGradeAndClassroom(Request $request)
{
    $validated = $request->validate([
        'grade' => 'required|string',   // مثال: 3
        'section' => 'required|string',  // مثال: A
    ]);

    // منجيب الطلاب يلي بنفس الصف والشعبة
    $students = Student::where('grade', $validated['grade'])
        ->where('section', $validated['section'])
        ->with('user')  // إذا بدك معلومات المستخدم كمان
        ->get();

    if ($students->isEmpty()) {
        return response()->json([
            'message' => 'لا يوجد طلاب بهذا الصف والشعبة',
        ], 404);
    }

    return response()->json([
        'grade' => $validated['grade'],
        'section' => $validated['section'],
        'students' => $students,
    ]);
}


//________________________________________________________________________________________
    // تابع لجلب الطلاب حسب الشعبة
    public function studentsByGrade($grade)
    {
        $students = Student::where('grade', $grade)->with('user:id,username')->get();

        return response()->json($students);
    }



}
