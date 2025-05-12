<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\updateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function addEmployee(StoreEmployeeRequest $request)
    {
        $validatedData = $request->validated();

        $employees = Employee::create([
            'username' => $validatedData['username'],
            'father_name' => $validatedData['father_name'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
            'gender' => $validatedData['gender'],
            'job' => $validatedData['job'],
        ]);
        return response()->json([
            'message'=>'User Registered Successfully',
            'employee' => $employees,
            201]);
    }
    //______________________________________________________________________________
    public function showEmployee (){
        $employees= Employee::all();
        return response()->json($employees, 200);
    }
    //________________________________________________________________________________
    public function updateEmployee(updateEmployeeRequest $request,$id)
    {
        $validatedData = $request->validated();
        $employee= Employee::findorfail($id);
        $employee->update( $validatedData);
        return response()->json($employee, 200);
    }
}
