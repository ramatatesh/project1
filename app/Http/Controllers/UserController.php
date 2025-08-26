<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
//use Dotenv\Validator;
use App\Mail\SendCodeResetPassword;
use App\Models\ResetCodePassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Student;
//use App\Mail\ResetCodePassword as ResetCodePasswordMail;


class UserController extends Controller
{

   public function login(Request $request)
{
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string|min:8'
    ]);

    if (!Auth::attempt($request->only('username', 'password'))) {
        return response()->json([
            'message' => __('app.invalid_credentials'),
        ], 401);
    }

    $user = User::where('username', $request->username)->firstOrFail();
    $token = $user->createToken('auth_Token')->plainTextToken;


    $admain = $user->admain;

    return response()->json([
        'message' => __('app.login_success'),
        'User' => $user,
        'Role' => $user->role ?? null,
        'Grade_id'  => $admain ? $admain->grade_id : null,
        'Token' => $token,
    ], 201);
}

//____________________________________________________________________________________________________________
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message'=>__('app.logout')],201);

    }
    //________________________________________________________________________________________________________


    public function forgetPassword(Request $request){
        $data= $request->validate([
            'email' => 'required|email|exists:users',
        ]);
        ResetCodePassword::query()->where('email',$request['email'])->delete();
        $data['code']= mt_rand(100000,999999);
        $codeData = ResetCodePassword::create($data);

        Mail::to($data['email'])->send(new SendCodeResetPassword($codeData['code']));

        return response()->json(['message'=> trans(__('app.sent'))]);
    }
//__________________________________________________________________________________________
    public function userCheckCode(Request $request)
    {
        $request->validate([
            'code'=> 'required|string|exists:reset_code_passwords',
        ]);

        $passwordReset = ResetCodePassword::query()->firstWhere('code', $request['code']);

        if ($passwordReset['created_at'] < now()->subHour()) {
            $passwordReset->delete();
            return response()->json(['message'=> trans(__('app.expired'))], 422);
        }

        return response()->json([
            'email' => $passwordReset->email,
            'code' => $passwordReset->code,
            'message' => trans(__('app.check_password'))
        ], 200);
    }

    //______________________________________________________________________________________
    public function resetPassword(Request $request)
    {
        $input = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|exists:reset_code_passwords,code',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $passwordReset = ResetCodePassword::query()
            ->where('code', $input['code'])
            ->where('email', $input['email'])
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => trans(__('app.reset'))], 422);
        }

        if ($passwordReset->created_at < now()->subHour()) {
            $passwordReset->delete();
            return response()->json(['message'=> trans(__('app.expired'))], 422);
        }

        $user = User::query()->firstWhere('email', $passwordReset->email);
        $user->update([
            'password' => bcrypt($input['password']),
        ]);

        $passwordReset->delete();

        return response()->json(['message' => trans(__('app.reset_success'))], 200);
    }

}
