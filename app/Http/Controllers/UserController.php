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

    public function login(Request $request){
        $request->validate([
            'username'=>'required|string',
            'password'=>'required|string|min:8'
        ]);


        if(!Auth::attempt($request->only('username','password')))
            return response()->json([
                'message' => __('app.invalid_credentials'),],401);
        $user= User::where('username',$request->username)->FirstOrFail();
        $token= $user->createToken('auth_Token')->plainTextToken;

        return response()->json([
                'message' => __('app.login_success'),
                'User'=>$user,
                'Token'=>$token,
                ]
            ,201);
    }

//____________________________________________________________________________________________________________
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message'=>__('app.Logout Successful')],201);

    }
    //________________________________________________________________________________________________________

//    public function forgetPassword(Request $request)
//    {
//        $request->validate(['email' => 'required|email']);
//
//        $status = Password::sendResetLink(
//            $request->only('email')
//        );
//
//        return $status === Password::RESET_LINK_SENT
//            ? response()->json(['message' => 'تم إرسال الرابط إلى بريدك الإلكتروني.'])
//            : response()->json(['message' => 'فشل في إرسال الرابط.'], 400);
//    }
//___________________________________________________________________________________________________________
//    public function resetPassword(Request $request)
//    {
//        $request->validate([
//            'token' => 'required',
//            'email' => 'required|email',
//            'password' => 'required|confirmed|min:8',
//        ]);
//
//        $status = Password::reset(
//            $request->only('email', 'password', 'password_confirmation', 'token'),
//            function ($user, $password) {
//                $user->forceFill([
//                    'password' => bcrypt($password)
//                ])->save();
//
//            }
//        );
//
//        return $status === Password::PASSWORD_RESET
//            ? response()->json(['message' => 'تم تغيير كلمة المرور بنجاح.'])
//            : response()->json(['message' => 'فشل التغيير.'], 400);
//    }
    //_________________________________________________________________________________________
    public function UpdateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'email' => 'required|email',
            'new_email' => 'sometimes|email|unique:users,email',
            'password' => 'sometimes|string|min:8',
            'location' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $user = User::where('username', $validated['username'])
            ->where('email', $validated['email'])
            ->first();

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود.'], 404);
        }

        if (isset($validated['new_username'])) {
            $user->username = $validated['new_username'];
        }

        if (isset($validated['new_email'])) {
            $user->email = $validated['new_email'];
        }

        if (isset($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        if (isset($validated['location'])) {
            $user->location = $validated['location'];
        }

        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }

        $user->save();

        return response()->json([
            'message' => 'تم تحديث بيانات المستخدم بنجاح.',
            'user' => $user
        ]);
    }
    //___________________________________________________________________________________
    public function deleteUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'father_name' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->where('father_name', $request->father_name)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->delete();

        return response()->json(['message' => ' deleted successfully.'], 200);
    }
//_________________________________________________________________________________________
    public function forgetPassword(Request $request){
        $data= $request->validate([
            'email' => 'required|email|exists:users',
        ]);
        ResetCodePassword::query()->where('email',$request['email'])->delete();
        $data['code']= mt_rand(100000,999999);
        $codeData = ResetCodePassword::create($data);
       // $codeData= ResetCodePassword::query()->create($data);
        Mail::to($data['email'])->send(new SendCodeResetPassword($codeData['code']));

      //  Mail::to($request['email'])->send(new ResetCodePassword($codeData['code']));
        return response()->json(['message'=> trans('password.sent')]);
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
            return response()->json(['message'=> trans('passwords.code_is_expire')], 422);
        }

        return response()->json([
            'email' => $passwordReset->email,
            'code' => $passwordReset->code,
            'message' => trans('passwords.code_is_valid')
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
            return response()->json(['message' => trans('passwords.code_or_email_invalid')], 422);
        }

        if ($passwordReset->created_at < now()->subHour()) {
            $passwordReset->delete();
            return response()->json(['message'=> trans('passwords.code_is_expire')], 422);
        }

        $user = User::query()->firstWhere('email', $passwordReset->email);
        $user->update([
            'password' => bcrypt($input['password']),
        ]);

        $passwordReset->delete();

        return response()->json(['message' => trans('passwords.reset_success')], 200);
    }

}
