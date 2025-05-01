<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
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
}
