<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
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

    $grade = Grade::where('name', $validatedData['grade'])->first();

    if (!$grade) {
        return response()->json([
            'message' => 'الصف الذي أدخلته غير موجود في النظام.',
        ], 400);
    }

    $user = User::create([
        'username' => $validatedData['username'],
        'first_name' => $validatedData['first_name'],
        'last_name' => $validatedData['last_name'],
        'father_name' => $validatedData['father_name'],
        'mother_name' => $validatedData['mother_name'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
        'phone' => $validatedData['phone'],
        'address' => $validatedData['address'],
        'birth_date' => $validatedData['birth_date'],
        'gender' => $validatedData['gender'],
        'nationality' => $validatedData['nationality'],
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
        'grade_id' => $grade->id,
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

public function getStudentsByGradeAndClassroom($gradeName, $sectionName)
{
    $grade = Grade::where('name', $gradeName)->first();

    if (!$grade) {
        return response()->json([
            'message' => 'الصف غير موجود.',
        ], 404);
    }

    $classroom = Classroom::where('grade_id', $grade->id)
        ->where('name', $sectionName)
        ->first();

    if (!$classroom) {
        return response()->json([
            'message' => 'الشعبة غير موجودة لهذا الصف.',
        ], 404);
    }

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
    public function updateStudent(UpdateStudentRequest $request,$userId)
    {
        $validatedData = $request->validated();
        $user = User::with('student')->find($userId);

        $password = isset($validatedData['password']) ? Hash::make($validatedData['password']) : null;
        $userData = [
            'email' => $validatedData['email'] ?? null,
            'phone' => $validatedData['phone'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'username' => $validatedData['username'] ?? null,
            'first_name' => $validatedData['first_name'] ?? null,
            'last_name' => $validatedData['last_name'] ?? null,
            'father_name' => $validatedData['father_name'] ?? null,
            'mother_name' => $validatedData['mother_name'] ?? null,
            'gender' => $validatedData['gender'] ?? null,
            'birth_date' => $validatedData['birth_date'] ?? null,
            'nationality' => $validatedData['nationality'] ?? null,
            'password' => $password
        ];
        $studentData = [
            'grade' => $validatedData['grade'] ?? null,
        ];
        $userData = array_filter($userData, fn($val) => !is_null($val));
        $studentData = array_filter($studentData, fn($val) => !is_null($val));

        $user->update($userData);
        if (!$user->student) {
            return response()->json(['message' => 'لا يوجد سجل طالب مرتبط بهذا المستخدم'], 404);
        }
        $user->student->update($studentData);

        return response()->json(['message' => 'تم التحديث بنجاح',
            'User'=>$user,
            200]);
    }
//____________________________________________________________________________________________________
    public function destroyStudent($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'الطالب غير موجود'], 404);
        }

        $user = $student->user;
        if ($user) {
            $user->delete();
        } else {
            $student->delete();
        }
        return response()->json(['message' => 'تم حذف الطالب والمستخدم بنجاح.'], 204);
    }

//___________________________________________________________________________________________________________________
    public function showStudent(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(["message" => "User not authenticated"], 401);
        }

        $student = Student::with('user')->where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(["message" => "الطالب غير موجود"], 404);
        }

        return response()->json([
            'message' => 'تم جلب بيانات الطالب بنجاح',
            'student' => $student
        ], 200);
    }

}

