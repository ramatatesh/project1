<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // تابع لتسجيل غيابات شعبة معينة
    public function takeAbsences(Request $request)
{
    $request->validate([
        'grade_id' => 'required|exists:grades,id',
        'classroom_id' => 'required|exists:classrooms,id',
        'date' => 'required|date',
        'absent_student_ids' => 'required|array',
        'absent_student_ids.*' => 'exists:students,id',
    ]);


    $students = Student::where('classroom_id', $request->classroom_id)->get();

    foreach ($students as $student) {
        $status = in_array($student->id, $request->absent_student_ids) ? 'absent' : 'present';

        Attendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'date' => $request->date,
            ],
            [
                'status' => $status,
                'classroom_id' => $request->classroom_id,
            ]
        );
    }

    return response()->json(['message' => 'تم تسجيل الغياب لهذا اليوم، وتم اعتبار الباقين حاضرين تلقائياً']);
}

//_________________________________________________________________________________________________

        public function getAbsenceCountBySection($grade_id,$classroom_id)
    {

        $students = Student::with('user')
            ->where($grade_id)
            ->where($classroom_id)
            ->get();

        $data = $students->map(function ($student) {
            $absenceCount = Attendance::where('student_id', $student->id)
                ->where('status', 'absent')
                ->count();

            return [
                'student_id' => $student->id,
                'name' => optional($student->user)->first_name . ' ' . optional($student->user)->last_name,
                'absence_days' => $absenceCount,
            ];
        });

        return response()->json([
            'classroom_id' => $classroom_id,
            'grade_id' => $grade_id,
            'students' => $data,
        ]);
    }
//________________________________________________________________________________________
    // تابع لعرض غيابات طالب معين (للويب)
    public function getStudentAbsences($studentId)
{
    $student = Student::find($studentId);

    if (!$student) {
        return response()->json(['message' => 'الطالب غير موجود'], 404);
    }

    $absentDates = Attendance::where('student_id', $studentId)
        ->where('status', 'absent')
        ->orderBy('date', 'desc')
        ->pluck('date')
        ->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        });

    return response()->json([
        'student_id' => $studentId,
        'absent_dates' => $absentDates
    ]);
}


//_______________________________________________________________________________________________
    // تابع لعرض غيابات طالب حسب التوكن(للموبايل)
    public function getStudentAbsencesMobile()
    {
        $user = auth()->user();

        if (!$user || !$user->student) {
            return response()->json(['message' => 'المستخدم ليس طالبًا أو غير مصادق عليه'], 403);
        }

        $student = $user->student;

        $absentDates = Attendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
            });

        return response()->json([
            'student_id' => $student->id,
            'absent_dates' => $absentDates
        ]);
    }



}
