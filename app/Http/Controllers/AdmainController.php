<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdmainRequest;
use App\Http\Requests\updateAdmainRequest;
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
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'father_name' => $validatedData['father_name'],
            'mother_name' => $validatedData['mother_name'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'birth_date' => $validatedData['birth_date'],
            'gender' => $validatedData['gender'],
            'nationality' => $validatedData['nationality'],
            'role' => 'admain'
        ]);

        $admains = Admain::create([
            'user_id' => $user->id,
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
    public function updateAdmain(updateAdmainRequest $request,$userId)
    {
        $validatedData = $request->validated();
        $user = User::with('admain')->find($userId);

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
        $admainData = [
            'grade' => $validatedData['grade'] ?? null,
            'specialization' => $validatedData['specialization'] ?? null,
        ];
        $userData = array_filter($userData, fn($val) => !is_null($val));
        $admainData = array_filter($admainData, fn($val) => !is_null($val));

        $user->update($userData);
        if (!$user->admain) {
            return response()->json(['message' => 'لا يوجد سجل مشرف مرتبط بهذا المستخدم'], 404);
        }
        $user->admain->update($admainData);

        return response()->json(['message' => 'تم التحديث بنجاح',
            'User'=>$user,
            200]);
    }
    //__________________________________________________________________________________________________

    public function destroyAdmin($id){
        $admin= Admain::find($id);
        if (!$admin) {
            return response()->json(['message' => 'المشرف غير موجود'], 404);
        }
        $admin->delete();
        return response()->json(['message' => ' deleted successfully.'], 204);
    }
    //__________________________________________________________________________________________________
    public function getAdmin()
    {
        $admins = Admain::all();
        return response()->json($admins);
    }
}
