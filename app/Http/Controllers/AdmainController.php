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
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'admain'
        ]);

        $admains = Admain::create([
            'user_id' => $user->id,
            'gender' => $validatedData['gender'],
            'phone' => $validatedData['phone'],
            'grade' => $validatedData['grade'],
            'address' => $validatedData['address'],
            'specialization' => $validatedData['specialization'],
        ]);
        return response()->json([
            'message'=>'User Registered Successfully',
            'User'=>$user,
            'admain' => $admains,
            201]);
    }
}
