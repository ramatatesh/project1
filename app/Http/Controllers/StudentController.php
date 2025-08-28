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


    $classrooms = Classroom::where('grade_id', $grade->id)->orderBy('name')->get();

    if ($classrooms->count() === 0) {
        foreach (['A', 'B', 'C', 'D'] as $name) {
            Classroom::create([
                'grade_id' => $grade->id,
                'name' => $name,
            ]);
        }
        $classrooms = Classroom::where('grade_id', $grade->id)->orderBy('name')->get();
    }

    $targetClassroom = null;
    foreach ($classrooms as $classroom) {
        if ($classroom->students()->count() < 35) {
            $targetClassroom = $classroom;
            break;
        }}

    if (!$targetClassroom) {
        return response()->json([
            'message' => __('app.classroom'),
        ], 400);}


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


    $password = isset($validatedData['password']) ? Hash::make($validatedData['password']) : null;

    $userData = [];

    if (isset($validatedData['email']) && $validatedData['email'] !== $user->email) {
        $userData['email'] = $validatedData['email'];
    }

    if (isset($validatedData['username']) && $validatedData['username'] !== $user->username) {
        $userData['username'] = $validatedData['username'];
    }

    $userData += [
        'phone'       => $validatedData['phone'] ?? $user->phone,
        'address'     => $validatedData['address'] ?? $user->address,
        'first_name'  => $validatedData['first_name'] ?? $user->first_name,
        'last_name'   => $validatedData['last_name'] ?? $user->last_name,
        'father_name' => $validatedData['father_name'] ?? $user->father_name,
        'mother_name' => $validatedData['mother_name'] ?? $user->mother_name,
        'gender'      => $validatedData['gender'] ?? $user->gender,
        'birth_date'  => $validatedData['birth_date'] ?? $user->birth_date,
        'nationality' => $validatedData['nationality'] ?? $user->nationality,
    ];

    if ($password) {
        $userData['password'] = $password;
    }

    $studentData = [];

    if (isset($validatedData['grade_id'])) {
        $studentData['grade_id'] = $validatedData['grade_id'];

        $grade = \App\Models\Grade::find($validatedData['grade_id']);
        if ($grade) {
            $studentData['grade'] = $grade->name;
        }
    }


    $user->update($userData);

    if (!$user->student) {
        return response()->json(['message' => __('app.NotStudent')], 404);
    }

   
    $user->student->update($studentData);

    return response()->json([
        'message' => __('app.update'),
        'User'    => $user->load('student'),
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


}

