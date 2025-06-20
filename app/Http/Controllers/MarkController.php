<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mark;
use App\Models\User;
use App\Models\Student;

class MarkController extends Controller
{
    // تابع اضافة علامات لشعبة
    public function storeMarks(Request $request)
{
    $request->validate([
        'grade_id' => 'required|exists:grades,id',
        'classroom_id' => 'required|exists:classrooms,id',
        'subject_id' => 'required|exists:subjects,id',
        'semester' => 'required|in:first,second',
        'type' => 'required|in:first_exam,second_exam,final',
        'marks' => 'required|array',
        'marks.*.student_id' => 'required|exists:students,id',
        'marks.*.mark' => 'nullable|string',
    ]);

    foreach ($request->marks as $markData) {
        if ($markData['mark'] !== null && $markData['mark'] !== '') {
            Mark::updateOrCreate(
                [
                    'student_id' => $markData['student_id'],
                    'subject_id' => $request->subject_id,
                    'semester' => $request->semester,
                    'type' => $request->type,
                ],
                [
                    'mark' => $markData['mark'],
                ]
            );
        }
    }

    return response()->json(['message' => 'تم حفظ العلامات بنجاح'], 200);
}

//_________________________________________________________________________________
// تابع عرض علامات طالب معين حسب التوكن
public function getStudentMarks()
{

     $student = auth()->user()->student;

    if (!$student) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $marks = Mark::with(['subject'])
                ->where('student_id', $student->id)
                ->get();

    return response()->json(['marks' => $marks]);
}
//_________________________________________________________________________________
//تابع يعرض الطلاب وعلاماتهم(للويب)
public function showStudentsWithMarks(Request $request)
{
    $request->validate([
        'grade_id' => 'required|exists:grades,id',
        'classroom_id' => 'required|exists:classrooms,id',
        'subject_id' => 'required|exists:subjects,id',
        'semester' => 'required|in:first,second',
        'type' => 'required|in:first_exam,second_exam,final',
    ]);

    $students = Student::where('grade_id', $request->query('grade_id'))
        ->where('classroom_id', $request->query('classroom_id'))
        ->with([
            'user',
            'marks' => function ($query) use ($request) {
                $query->where('subject_id', $request->query('subject_id'))
                      ->where('semester', $request->query('semester'))
                      ->where('type', $request->query('type'));
            }
        ])->get();

    $data = $students->map(function ($student) {
        return [
            'id' => $student->id,
            'name' => $student->user->username ?? null,
            'mark' => $student->marks->first()->mark ?? null,
        ];
    });

    return response()->json([
        'students' => $data,
    ]);
}
//_________________________________________________________________________________


}
