<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Models\Student;
use App\Models\User;
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
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'student'
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'father_name' => $validatedData['father_name'],
            'mother_name' => $validatedData['mother_name'],
            'birth_date' => $validatedData['birth_date'],
            'gender' => $validatedData['gender'],
            'phone' => $validatedData['phone'],
            'grade' => $validatedData['grade'],
            'address' => $validatedData['address']
        ]);
        return response()->json([
            'message'=>'User Registered Successfully',
            'User'=>$user,
            'Student' => $student,
            201]);
    }
//________________________________________________________________________________________



}
