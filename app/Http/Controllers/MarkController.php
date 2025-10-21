<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mark;
use App\Models\User;
use App\Models\Student;
use App\Models\Subject;
class MarkController extends Controller
{

// تابع لاضافة علامات الطلاب
public function storeMarks(Request $request)

{
    $request->validate([
        'grade_id' => 'required|exists:grades,id',
        'classroom_id' => 'required|exists:classrooms,id',
        'subject_id' => 'required|exists:subjects,id',
        'semester' => 'required|in:first,second',
        'type' => 'required|in:exam,final,quiz',
        'max_mark' => 'required|numeric|min:0',
        'marks' => 'required|array',
        'marks.*.student_id' => 'required|exists:students,id',
        'marks.*.mark' => 'nullable|numeric',
    ]);


    $finalMaxMark = $request->max_mark;

    foreach ($request->marks as $markData) {
        if ($markData['mark'] !== null && $markData['mark'] !== '') {
            if ($markData['mark'] > $finalMaxMark) {
                return response()->json([
                    'message' => 'العلامة المدخلة للطالب ID ' . $markData['student_id'] .
                                ' أكبر من العلامة العظمى المسموح بها (' . $finalMaxMark . ').'
                ], 422);
            }

            Mark::updateOrCreate(
                [
                    'student_id' => $markData['student_id'],
                    'subject_id' => $request->subject_id,
                    'semester' => $request->semester,
                    'type' => $request->type,
                    'max_mark' => $finalMaxMark,
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

public function getStudentMarks()
{
    $student = auth()->user()->student;

    if (!$student) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $marks = Mark::with(['subject'])
                ->where('student_id', $student->id)
                ->get()
                ->map(function ($mark) {
                    return [
                        'id' => $mark->id,
                        'subject_id' => $mark->subject_id,
                        'student_id' => $mark->student_id,
                        'mark' => $mark->mark,
                        'max_mark' => $mark->max_mark,
                        'semester' => $mark->semester,
                        'type' => $mark->type,
                        'created_at' => $mark->created_at->format('Y-m-d'),
                        'subject' => $mark->subject,
                    ];
                });

    return response()->json(['marks' => $marks]);
}
//_________________________________________________________________________________
public function showStudentsWithMarks(Request $request)
{
    $request->validate([
        'grade_id' => 'required|exists:grades,id',
        'classroom_id' => 'required|exists:classrooms,id',
        'subject_id' => 'required|exists:subjects,id',
        'semester' => 'required|in:first,second',
        'type' => 'required|in:exam,final',
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
            'max_mark' => $student->marks->first()->max_mark ?? null,
        ];
    });

    return response()->json([
        'students' => $data,
    ]);
}
//_________________________________________________________________________________
public function getStudentAverages(Request $request)
{
    $student = auth()->user()->student;

    if (!$student) {
        return response()->json(['error' => 'Student not found'], 404);
    }


    $marks = Mark::where('student_id', $student->id)
        ->select('semester', 'type', 'mark', 'max_mark', 'subject_id')
        ->get();


    $types = [
        'term1_exam' => ['semester' => 'first', 'type' => 'exam'],
        'term1_final' => ['semester' => 'first', 'type' => 'final'],
        'term2_exam' => ['semester' => 'second', 'type' => 'exam'],
    ];

    $averages = [];

    foreach ($types as $key => $filter) {
        $filteredMarks = $marks->filter(function ($mark) use ($filter) {
            return $mark->semester == $filter['semester'] && $mark->type == $filter['type'];
        });


        $totalPercentage = 0;
        $subjectsCount = Subject::count();

        foreach (Subject::all() as $subject) {
            $subjectMark = $filteredMarks->firstWhere('subject_id', $subject->id);

            if ($subjectMark) {
                $percentage = ($subjectMark->mark / $subjectMark->max_mark) * 100;
            } else {
                $percentage = 0;
            }

            $totalPercentage += $percentage;
        }

        $averages[$key] = round($totalPercentage / $subjectsCount, 2);
    }

    return response()->json($averages);
}
//_________________________________________________________________________________

// الاضافااااااات

// تابع لإضافة علامات طلاب شعبة كاملة
public function addMarks(Request $request)
{
    $request->validate([
        'grade_id' => 'required|exists:grades,id',
        'classroom_id' => 'required|exists:classrooms,id',
        'subject_id' => 'required|exists:subjects,id',
        'semester' => 'required|in:first,second',
        'type' => 'required|in:exam,final,quiz',
        'max_mark' => 'required|numeric|min:0',
        'marks' => 'required|array',
        'marks.*.student_id' => 'required|exists:students,id',
        'marks.*.mark' => 'nullable|numeric',
    ]);

    $finalMaxMark = $request->max_mark;

    foreach ($request->marks as $markData) {
        if ($markData['mark'] !== null && $markData['mark'] !== '') {
            if ($markData['mark'] > $finalMaxMark) {
                return response()->json([
                    'message' => 'العلامة المدخلة للطالب ID ' . $markData['student_id'] .
                                ' أكبر من العلامة العظمى المسموح بها (' . $finalMaxMark . ').'
                ], 422);
            }


            Mark::create([
                'student_id' => $markData['student_id'],
                'subject_id' => $request->subject_id,
                'semester' => $request->semester,
                'type' => $request->type,
                'max_mark' => $finalMaxMark,
                'mark' => $markData['mark'],
            ]);
        }
    }

    return response()->json(['message' => 'تمت إضافة العلامات بنجاح'], 200);
}



// تابع تعديل علامة
public function updateMark(Request $request)
{
    $request->validate([
        'grade_id' => 'required|exists:grades,id',
        'classroom_id' => 'required|exists:classrooms,id',
        'subject_id' => 'required|exists:subjects,id',
        'semester' => 'required|in:first,second',
        'type' => 'required|in:exam,final,quiz',
        'marks' => 'required|array',
        'marks.*.student_id' => 'required|exists:students,id',
        'marks.*.mark' => 'nullable|numeric|min:0',
        'marks.*.max_mark' => 'nullable|numeric|min:0',
    ]);

    foreach ($request->marks as $markData) {
        $mark = Mark::where('student_id', $markData['student_id'])
            ->where('subject_id', $request->subject_id)
            ->where('semester', $request->semester)
            ->where('type', $request->type)
            ->first();

        if (!$mark) {
            continue;
        }


        $finalMaxMark = $markData['max_mark'] ?? $mark->max_mark;
        if (isset($markData['mark']) && $markData['mark'] > $finalMaxMark) {
            return response()->json([
                'message' => 'العلامة للطالب ID ' . $markData['student_id'] . ' أكبر من العلامة العظمى (' . $finalMaxMark . ').'
            ], 422);
        }


        $mark->update([
            'mark' => $markData['mark'] ?? $mark->mark,
            'max_mark' => $markData['max_mark'] ?? $mark->max_mark,
        ]);
    }

    return response()->json([
        'message' => 'تم تعديل علامات الشعبة بنجاح',
    ], 200);
}



// تابع يحذف علامة طالب معين
public function deleteMark(Request $request)
{
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'subject_id' => 'required|exists:subjects,id',
        'semester' => 'required|in:first,second',
        'type' => 'required|in:exam,final,quiz',
    ]);

    $student = Student::findOrFail($request->student_id);

    $mark = Mark::where('student_id', $student->id)
                ->where('subject_id', $request->subject_id)
                ->where('semester', $request->semester)
                ->where('type', $request->type)
                ->first();

    if (!$mark) {
        return response()->json([
            'message' => 'لا توجد علامة لهذا الطالب في البيانات المحددة'
        ], 404);
    }

    $mark->delete();

    return response()->json([
        'message' => 'تم حذف علامة الطالب بنجاح'
    ], 200);
}






}
