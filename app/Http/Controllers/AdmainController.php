<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdmainRequest;
use App\Models\Admain;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdmainController extends Controller
{
    public function addAdmain(StoreAdmainRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'username' => $validatedData['username'],
            'father_name' => $validatedData['father_name'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'admain'
        ]);

        $admains = Admain::create([
            'user_id' => $user->id,
            'gender' => $validatedData['gender'],
            'grade' => $validatedData['grade'],
            'specialization' => $validatedData['specialization'],
        ]);
        return response()->json([
            'message'=>'User Registered Successfully',
            'User'=>$user,
            'admain' => $admains,
            201]);
    }
    //___________________________________________________________________________________
}
