<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function addTeacher(StoreTeacherRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'username' => $validatedData['username'],
            'father_name'=> $validatedData['father_name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'address' => $validatedData['address'],
            'phone' => $validatedData['phone'],
            'role' => 'teacher'
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'gender' => $validatedData['gender'],
            'grade' => $validatedData['grade'],
            'specialization' => $validatedData['specialization'],
            'teaching_years' => $validatedData['teaching_years']
        ]);
        return response()->json([
            'message'=>'User Registered Successfully',
            'User'=>$user,
            'teacher' => $teacher,
            201]);
    }
    //________________________________________________________________________________________
    public function updateTeacher(UpdateTeacherRequest $request,$userId)
    {
        $validatedData = $request->validated();
        $user = User::with('teacher')->find($userId);

        $password = isset($validatedData['password']) ? Hash::make($validatedData['password']) : null;
        $userData = [
            'email' => $validatedData['email'] ?? null,
            'phone' => $validatedData['phone'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'username' => $validatedData['username'] ?? null,
            'father_name' => $validatedData['father_name'] ?? null,
            'password' => $password
        ];
        $teacherData = [
            'gender' => $validatedData['gender'] ?? null,
            'grade' => $validatedData['grade'] ?? null,
            'specialization' => $validatedData['specialization'] ?? null,
            'teaching_years' => $validatedData['teaching_years'] ?? null
        ];
        $userData = array_filter($userData, fn($val) => !is_null($val));
        $teacherData = array_filter($teacherData, fn($val) => !is_null($val));

        $user->update($userData);
        if (!$user->teacher) {
            return response()->json(['message' => 'لا يوجد سجل معلم مرتبط بهذا المستخدم'], 404);
        }
        $user->teacher->update($teacherData);

        return response()->json(['message' => 'تم التحديث بنجاح',
            'User'=>$user,
            200]);
    }
}
