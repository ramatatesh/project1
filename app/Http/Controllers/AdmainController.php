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
    public function updateAdmain(updateAdmainRequest $request, $userId)
    {
        $validatedData = $request->validated();

        $user = User::with('admain')->find($userId);
        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        // فقط عند وجود كلمة مرور جديدة
        $password = isset($validatedData['password']) ? Hash::make($validatedData['password']) : null;

        // تأكد من عدم تحديث username و email إذا لم تتغير فعليًا
        $userData = [];

        if (isset($validatedData['email']) && $validatedData['email'] !== $user->email) {
            $userData['email'] = $validatedData['email'];
        }

        if (isset($validatedData['username']) && $validatedData['username'] !== $user->username) {
            $userData['username'] = $validatedData['username'];
        }

        // باقي البيانات العادية
        $userData += [
            'phone' => $validatedData['phone'] ?? $user->phone,
            'address' => $validatedData['address'] ?? $user->address,
            'first_name' => $validatedData['first_name'] ?? $user->first_name,
            'last_name' => $validatedData['last_name'] ?? $user->last_name,
            'father_name' => $validatedData['father_name'] ?? $user->father_name,
            'mother_name' => $validatedData['mother_name'] ?? $user->mother_name,
            'gender' => $validatedData['gender'] ?? $user->gender,
            'birth_date' => $validatedData['birth_date'] ?? $user->birth_date,
            'nationality' => $validatedData['nationality'] ?? $user->nationality,
        ];

        if ($password) {
            $userData['password'] = $password;
        }

        $admainData = [
            'grade' => $validatedData['grade'] ?? $user->admain->grade,
            'specialization' => $validatedData['specialization'] ?? $user->admain->specialization,
        ];

        // تحديث البيانات
        $user->update($userData);

        if (!$user->admain) {
            return response()->json(['message' => 'لا يوجد سجل مشرف مرتبط بهذا المستخدم'], 404);
        }

        $user->admain->update($admainData);

        return response()->json([
            'message' => 'تم التحديث بنجاح',
            'User' => $user,
        ], 200);
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
        $admins = Admain::with('user')->get();
        return response()->json($admins);
    }
}
