<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Models\User;
use App\Models\Note;
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

        $student = Student::create([
            'user_id' => $user->id,
            'mother_name' => $validatedData['mother_name'],
            'birth_date' => $validatedData['birth_date'],
            'gender' => $validatedData['gender'],
            'grade' => $validatedData['grade'],
        ]);
        return response()->json([
            'message'=>'User Registered Successfully',
            'User'=>$user,
            'Student' => $student,
            201]);
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
        'father_name' => $validatedData['father_name'] ?? null,
        'password' => $password
        ];
    $studentData = [
        'mother_name' => $validatedData['mother_name'] ?? null,
        'birth_date' => $validatedData['birth_date'] ?? null,
        'gender' => $validatedData['gender'] ?? null,
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
//________________________________________________________________________________________
    // تابع لجلب الطلاب حسب الشعبة
    public function studentsByGrade($grade)
    {
        $students = Student::where('grade', $grade)->with('user:id,username')->get();

        return response()->json($students);
    }



}
