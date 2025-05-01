<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function addEmployee(StoreEmployeeRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'username' => $validatedData['username'],
            'father_name' => $validatedData['father_name'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'employee'
        ]);

        $employees = Employee::create([
            'user_id' => $user->id,
            'gender' => $validatedData['gender'],
            'job' => $validatedData['job'],
        ]);
        return response()->json([
            'message'=>'User Registered Successfully',
            'User'=>$user,
            'employee' => $employees,
            201]);
    }
    //______________________________________________________________________________
    public function showEmployee (){
        $employees = Employee::with('user')->get();
        return response()->json($employees, 200);
    }
}
