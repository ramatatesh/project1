<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    // تابع لاضافة معلم
    public function addTeacher(StoreTeacherRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'username' => $validatedData['username'],
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'father_name'=> $validatedData['father_name'],
            'mother_name'=> $validatedData['mother_name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'address' => $validatedData['address'],
            'phone' => $validatedData['phone'],
            'birth_date' => $validatedData['birth_date'],
            'gender' => $validatedData['gender'],
            'nationality' => $validatedData['nationality'],
            'role' => 'teacher'
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'specialization' => $validatedData['specialization'],
            'start_date' => $validatedData['start_date'],
            'subject_id'=>$validatedData['subject_id'],
        ]);
        return response()->json([
            'message'=>'User Registered Successfully',
            'User'=>$user,
            'teacher' => $teacher,
            201]);
    }
    //________________________________________________________________________________________
    // تابع لتعديل بيانات معلم
    public function updateTeacher(UpdateTeacherRequest $request,$userId)
    {
        $validatedData = $request->validated();
        $user = User::with('teacher')->find($userId);
        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $password = isset($validatedData['password']) ? Hash::make($validatedData['password']) : null;
        $userData = [];

        if (isset($validatedData['email']) && $validatedData['email'] !== $user->email) {
            $userData['email'] = $validatedData['email'];
        }

        if (isset($validatedData['username']) && $validatedData['username'] !== $user->username) {
            $userData['username'] = $validatedData['username'];
        }
        $userData += [
            'email' => $validatedData['email'] ?? null,
            'phone' => $validatedData['phone'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'username' => $validatedData['username'] ?? null,
            'first_name' => $validatedData['first_name'] ?? null,
            'last_name' => $validatedData['last_name'] ?? null,
            'father_name' => $validatedData['father_name'] ?? null,
            'mother_name' => $validatedData['mother_name'] ?? null,
            'birth_date' => $validatedData['birth_date'] ?? null,
            'nationality' => $validatedData['nationality'] ?? null,
            'gender' => $validatedData['gender'] ?? null,
            'password' => $password
        ];
        if ($password) {
            $userData['password'] = $password;
        }
        $teacherData = [
            'specialization' => $validatedData['specialization'] ?? null,
            'start_date' =>$validatedData['start_date'] ?? null,
            'subject_id' => $validatedData['subject_id'] ?? null,
        ];
        $userData = array_filter($userData, fn($val) => !is_null($val));
        $teacherData = array_filter($teacherData, fn($val) => !is_null($val));

        $user->update($userData);
        if (!$user->teacher) {
            return response()->json(['message' => 'لا يوجد سجل معلم مرتبط بهذا المستخدم'], 404);
        }
        $user->teacher->update($teacherData);

        return response()->json(['message' => 'تم التحديث بنجاح',
            'User'=>$user,
            200]);
    }
    //______________________________________________________________________________________________________
   // تابع لحذف معلم
    public function destroyTeacher($id){
        $teacher= Teacher::find($id);
        if (!$teacher) {
            return response()->json(['message' => 'المعلم غير موجود'], 404);
        }
        $teacher->delete();
        return response()->json(['message' => ' deleted successfully.'], 204);
    }

    //____________________________________________________________________________________________


    //تابع جلب المعلمين حسب المادة
    public function getTeachersBySubject($subject_id)
    {
    $subject = Subject::with('teachers')->find($subject_id);

    if (!$subject) {
        return response()->json(['message' => 'المادة غير موجودة.'], 404);
    }

    return response()->json([
        'subject' => $subject->name,
        'teachers' => $subject->teachers,
    ]);
    }

    //____________________________________________________________________________________________

    public function getTeacher()
    {
        $teachers = Teacher::with(['user', 'subject:id,name'])->get();

        $formatted = $teachers->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'username' => $teacher->user->username,
                'first_name' => $teacher->user->first_name,
                'last_name' => $teacher->user->last_name,
                'father_name' => $teacher->user->father_name,
                'mother_name' => $teacher->user->mother_name,
                'gender' => $teacher->user->gender,
                'birth_date' => $teacher->user->birth_date,
                'email' => $teacher->user->email,
                'phone' => $teacher->user->phone,
                'address' => $teacher->user->address,
                'nationality' => $teacher->user->nationality,
                'specialization' => $teacher->specialization,
                'start_date' => $teacher->start_date,
                'subject' => $teacher->subject ? $teacher->subject->name : null, // اسم المادة بدلًا من subject_id
            ];
        });

        return response()->json($formatted);
    }

}
