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
            'father_name'=> $validatedData['father_name'],
            'mother_name'=> $validatedData['mother_name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'address' => $validatedData['address'],
            'phone' => $validatedData['phone'],
            'role' => 'teacher'
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'gender' => $validatedData['gender'],
            'grade' => $validatedData['grade'],
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

        $password = isset($validatedData['password']) ? Hash::make($validatedData['password']) : null;
        $userData = [
            'email' => $validatedData['email'] ?? null,
            'phone' => $validatedData['phone'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'username' => $validatedData['username'] ?? null,
            'father_name' => $validatedData['father_name'] ?? null,
            'password' => $password
        ];
        $teacherData = [
            'gender' => $validatedData['gender'] ?? null,
            'grade' => $validatedData['grade'] ?? null,
            'specialization' => $validatedData['specialization'] ?? null,
            'teaching_years' => $validatedData['teaching_years'] ?? null
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
}
