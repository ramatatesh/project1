<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        ]);

        // ربط المواد
        $teacher->subjects()->attach($validatedData['subject_ids']);

        return response()->json([
            'message' => 'تم إنشاء المعلم بنجاح',
            'user' => $user,
            'teacher' => $teacher,
        ], 201);
    }

    //________________________________________________________________________________________
    // تابع لتعديل بيانات معلم
    public function updateTeacher(UpdateTeacherRequest $request, $userId)
    {
        $validatedData = $request->validated();

        $user = User::with('teacher')->find($userId);
        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $password = isset($validatedData['password']) ? Hash::make($validatedData['password']) : null;

        $userData = [
            'email' => $validatedData['email'] ?? $user->email,
            'username' => $validatedData['username'] ?? $user->username,
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

        $teacherData = [
            'specialization' => $validatedData['specialization'] ?? $user->teacher->specialization,
            'start_date' => $validatedData['start_date'] ?? $user->teacher->start_date,
        ];

        $user->update(array_filter($userData, fn($val) => !is_null($val)));

        if (!$user->teacher) {
            return response()->json(['message' => 'لا يوجد سجل معلم مرتبط بهذا المستخدم'], 404);
        }

        $user->teacher->update(array_filter($teacherData, fn($val) => !is_null($val)));

        // تحديث المواد التي يدرسها المعلم إن وُجدت
        if (isset($validatedData['subject_ids']) && is_array($validatedData['subject_ids'])) {
            $user->teacher->subjects()->sync($validatedData['subject_ids']);
        }

        return response()->json([
            'message' => 'تم التحديث بنجاح',
            'User' => $user->load('teacher.subjects')
        ], 200);
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
    $subject = Subject::with(['teachers.user'])->find($subject_id);

    if (!$subject) {
        return response()->json(['message' => 'المادة غير موجودة.'], 404);
    }

    $teachers = $subject->teachers->map(function ($teacher) {
        return [
            'teacher_id' => $teacher->id,
            'username' => $teacher->user->username
        ];
    });

    return response()->json([
        'subject' => $subject->name,
        'teachers' => $teachers,
    ]);
}

    //____________________________________________________________________________________________
    // عرض كل المعلمين
    public function getTeacher()
    {
        $teachers = Teacher::with(['user', 'subjects:id,name'])->get();

        $formatted = $teachers->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'user_id' => $teacher->user->id, // ✅ إضافة user_id
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
                'subjects' => $teacher->subjects->pluck('name'),
            ];
        });

        return response()->json($formatted);
    }

//___________________________________________________________________________________________
   // عرض الملف الشخصي للمعلم
   public function showTeacher(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(["message" => "User not authenticated"], 401);
    }

    $teacher = Teacher::with('user')->where('user_id', $user->id)->first();

    if (!$teacher) {
        return response()->json(["message" => "المعلم غير موجود"], 404);
    }

    $subjectNames = $teacher->subjects()->pluck('name');

    return response()->json([
        'message' => 'تم جلب بيانات المعلم بنجاح',
        'teacher' => $teacher,
        'subjects' => $subjectNames,
    ], 200);
}
//___________________________________________________________________________________________


}
