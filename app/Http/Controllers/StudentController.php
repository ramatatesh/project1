<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Models\User;
use App\Models\Note;
use App\Models\Grade;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Services\FirebaseService;

class StudentController extends Controller
{
public function addStudent(StoreStudentRequest $request)
{
    $validatedData = $request->validated();

    $grade = Grade::where('name', $validatedData['grade'])->first();

    if (!$grade) {
        return response()->json([
            'message' => __('app.class'),
        ], 400);
    }

    $user = User::create([
        'username' => $validatedData['username'],
        'first_name' => $validatedData['first_name'],
        'last_name' => $validatedData['last_name'],
        'father_name' => $validatedData['father_name'],
        'mother_name' => $validatedData['mother_name'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
        'phone' => $validatedData['phone'],
        'address' => $validatedData['address'],
        'birth_date' => $validatedData['birth_date'],
        'gender' => $validatedData['gender'],
        'nationality' => $validatedData['nationality'],
        'role' => 'student'
    ]);


    $grade = Grade::firstOrCreate(['name' => $validatedData['grade']]);

    $classrooms = Classroom::where('grade_id', $grade->id)->get();

    if ($classrooms->count() === 0) {
        foreach (['A', 'B', 'C', 'D'] as $name) {
            Classroom::create([
                'grade_id' => $grade->id,
                'name' => $name,
            ]);
        }

        $classrooms = Classroom::where('grade_id', $grade->id)->get();
    }


    $availableClassrooms = $classrooms->filter(function ($classroom) {
        return $classroom->students()->count() < 35;
    });

    if ($availableClassrooms->isEmpty()) {
        return response()->json([
            'message' => __('app.classroom'),
        ], 400);
    }


    $targetClassroom = $availableClassrooms->sortBy(function ($classroom) {
        return $classroom->students()->count();
    })->first();


    $student = Student::create([
        'user_id' => $user->id,
        'grade_id' => $grade->id,
        'grade' => $validatedData['grade'],
        'classroom_id' => $targetClassroom->id,
        'section' => $targetClassroom->name,
    ]);

    return response()->json([
        'message' => __('app.addStudent'),
        'User' => $user,
        'Student' => $student,
    ], 201);
}


//________________________________________________________________________________________

    public function getStudentsByGradeAndClassroom($gradeId, $classroomId)
    {
        $grade = Grade::find($gradeId);

        if (!$grade) {
            return response()->json([
                'message' => __('app.classNotFound'),
            ], 404);
        }

        $classroom = Classroom::where('id', $classroomId)
            ->where('grade_id', $gradeId)
            ->first();

        if (!$classroom) {
            return response()->json([
                'message' => __('app.classroomNotFound'),
            ], 404);
        }

        $students = Student::where('classroom_id', $classroom->id)
            ->with('user')
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'message' => __('app.studentsNotFound'),
            ], 404);
        }

        return response()->json([
            'grade' => $grade->name,
            'section' => $classroom->name,
            'students' => $students,
        ]);
    }

//________________________________________________________________________________________
    public function updateStudent(UpdateStudentRequest $request, $userId)
    {
        $validatedData = $request->validated();

        $user = User::with('student')->find($userId);
        if (!$user) {
            return response()->json(['message' => __('app.user')], 404);
        }

        // فقط عند وجود كلمة مرور جديدة
        $password = isset($validatedData['password']) ? Hash::make($validatedData['password']) : null;

        // بناء البيانات فقط إذا تغيّرت عن القديمة
        $userData = [];

        if (isset($validatedData['email']) && $validatedData['email'] !== $user->email) {
            $userData['email'] = $validatedData['email'];
        }

        if (isset($validatedData['username']) && $validatedData['username'] !== $user->username) {
            $userData['username'] = $validatedData['username'];
        }

        // باقي البيانات الاعتيادية
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

        $studentData = [
            'grade' => $validatedData['grade'] ?? $user->student->grade,
        ];

        // تحديث البيانات
        $user->update($userData);

        if (!$user->student) {
            return response()->json(['message' => __('app.NotStudent')], 404);
        }

        $user->student->update($studentData);

        return response()->json([
            'message' => __('app.update'),
            'User' => $user,
        ], 200);
    }
//____________________________________________________________________________________________________
    public function destroyStudent($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => __('app.student')], 404);
        }

        $user = $student->user;
        if ($user) {
            $user->delete();
        } else {
            $student->delete();
        }
        return response()->json(['message' => __('app.deleteStudent')], 204);
    }

//___________________________________________________________________________________________________________________
    public function showStudent(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(["message" => __("app.unauthenticated")], 401);
        }

        $student = Student::with('user')->where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(["message" => __("app.student")], 404);
        }

        return response()->json([
            'message' => __('app.showStudent'),
            'student' => $student
        ], 200);
    }
//_____________________________________________________________________________________________________
// توابع الاشعارات
    // تابع لحفظ توكن الطالب بالداتا بيز
    public function updateFcmToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required|string',
    ]);


    $student = auth()->user();

    $student->fcm_token = $request->fcm_token;
    $student->save();

    return response()->json(['message' => 'تم تحديث التوكن بنجاح']);
}


public function sendNotificationToSelf()
{
    $student = auth()->user();

    if (!$student->fcm_token) {
        return response()->json(['error' => 'لا يوجد FCM Token لهذا الطالب'], 400);
    }

    $firebase = new FirebaseService();

    try {
        $firebase->sendNotificationToDevice(
            $student->fcm_token,
            'تنبيه',
            'هذا إشعار موجه لك شخصيًا 🤓'
        );

        return response()->json(['message' => 'تم إرسال الإشعار']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'فشل الإرسال: ' . $e->getMessage()], 500);
    }
}

}

