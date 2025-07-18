<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WeeklyScheduleImport;
use App\Models\WeeklySchedule;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Lesson;

class WeeklyScheduleController extends Controller
{
// تابع لانشاء جدول اسبوعي
public function storeWeeklySchedule(Request $request)
{
    $request->validate([
        'classroom_id' => 'required|exists:classrooms,id',
        'semester' => 'required|in:first,second',
        'lessons' => 'required|array|max:35',
        'lessons.*.day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday',
        'lessons.*.subjects' => 'required|array|max:7',
        'lessons.*.subjects.*.subject_id' => 'required|exists:subjects,id',
        'lessons.*.subjects.*.teacher_id' => 'required|exists:teachers,id',
        'lessons.*.subjects.*.time' => 'required|date_format:H:i',
    ]);

    $existingSchedule = WeeklySchedule::where('classroom_id', $request->classroom_id)
                                      ->where('semester', $request->semester)
                                      ->first();

    if ($existingSchedule) {
        return response()->json([
            'message' => 'يوجد بالفعل جدول أسبوعي لهذه الشعبة في هذا الفصل.',
        ], 409);
    }

    $schedule = WeeklySchedule::create([
        'classroom_id' => $request->classroom_id,
        'semester' => $request->semester,
    ]);

    foreach ($request->lessons as $dayBlock) {
        $day = $dayBlock['day'];
        $subjects = $dayBlock['subjects'];

        foreach ($subjects as $lesson) {
            Lesson::create([
                'weekly_schedule_id' => $schedule->id,
                'subject_id' => $lesson['subject_id'],
                'teacher_id' => $lesson['teacher_id'],
                'day' => $day,
                'time' => $lesson['time'],
            ]);
        }
    }

    return response()->json(['message' => 'تم إنشاء الجدول الأسبوعي بنجاح.'], 201);
}




//________________________________________________________________________________________

 // تابع يعرض الجدول الأسبوعي بناءً على توكن الطالب (للطالب)
public function getWeeklySchedule(Request $request)
{
    $student = auth()->user()->student;

    if (!$student) {
        return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
    }

    $classroom = $student->classroom;

    if (!$classroom) {
        return response()->json(['message' => 'الطالب غير مرتبط بأي شعبة.'], 404);
    }

    $semester = $request->input('semester');
    if (!in_array($semester, ['first', 'seconde'])) {
        return response()->json(['message' => 'يرجى تحديد الفصل.'], 422);
    }

    $schedules = WeeklySchedule::where('classroom_id', $classroom->id)
        ->where('semester', $semester)
        ->with(['lessons.subject:id,name', 'lessons.teacher.user:id,username'])
        ->get();

    if ($schedules->isEmpty()) {
        return response()->json(['message' => 'لا يوجد جدول أسبوعي لهذه الشعبة في هذا الفصل.'], 404);
    }

    $result = $schedules->map(function ($schedule) {
        $grouped = $schedule->lessons->groupBy('day')->map(function ($lessons) {
            return $lessons->map(function ($lesson) {
                $subjectName = $lesson->subject->name ?? 'غير معروف';
                $slug = Str::slug($subjectName, '_');
                $imageUrl = asset("images/subjects/{$slug}.png");

                return [
                    'time' => $lesson->time,
                    'subject' => $subjectName,
                    'teacher' => $lesson->teacher->user->username ?? 'غير معروف',
                    'image' => $imageUrl,
                ];
            })->sortBy('time')->values();
        });

        return [
            'semester' => $schedule->semester,
            'weekly_schedule' => $grouped,
        ];
    });

    return response()->json([
        'student' => $student->user->username,
        'classroom' => $classroom->name,
        'grade' => $classroom->grade->name,
        'schedules' => $result,
    ]);
}



//________________________________________________________________________________________
//تابع لعرض الجدول الاسبوعي بناء على الصف والشعبة و الفصل (للمشرف )

public function showWeeklySchedule(Request $request)
{
    $request->validate([
        'classroom_id' => 'required|exists:classrooms,id',
        'semester' => 'required|in:first,second',
    ]);


    $classroom = Classroom::find($request->classroom_id);

    $schedule = WeeklySchedule::with(['lessons.subject', 'lessons.teacher.user'])
        ->where('classroom_id', $request->classroom_id)
        ->where('semester', $request->semester)
        ->first();

    if (!$schedule) {
        return response()->json(['message' => 'لا يوجد جدول أسبوعي لهذه الشعبة في هذا الفصل.'], 404);
    }

    $lessons = $schedule->lessons->map(function ($lesson) {
        return [
            'day' => $lesson->day,
            'time' => $lesson->time,
            'subject' => $lesson->subject->name ?? null,
            'teacher' => $lesson->teacher->user->username ?? null,
        ];
    });

    return response()->json([
        'classroom' => $classroom->grade . ' / ' . $classroom->section,
        'semester' => $request->semester,
        'lessons' => $lessons,
    ]);
}
//________________________________________________________________________________________
// تابع لتعديل جدول اسبوعي
public function updateWeeklySchedule(Request $request)
{
    $request->validate([
        'classroom_id' => 'required|exists:classrooms,id',
        'semester' => 'required|in:first,second',
        'lessons' => 'required|array',
        'lessons.*.day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday',
        'lessons.*.time' => 'required|date_format:H:i',
        'lessons.*.subject_id' => 'sometimes|exists:subjects,id',
        'lessons.*.teacher_id' => 'sometimes|exists:teachers,id',
    ]);

    $schedule = WeeklySchedule::where('classroom_id', $request->classroom_id)
        ->where('semester', $request->semester)
        ->first();

    if (!$schedule) {
        return response()->json(['message' => 'الجدول غير موجود'], 404);
    }

    foreach ($request->lessons as $lessonData) {
        $lesson = Lesson::where('weekly_schedule_id', $schedule->id)
                        ->where('day', $lessonData['day'])
                        ->where('time', $lessonData['time'])
                        ->first();

        if (!$lesson) {
            continue;
        }

        $lesson->subject_id = $lessonData['subject_id'] ?? $lesson->subject_id;
        $lesson->teacher_id = $lessonData['teacher_id'] ?? $lesson->teacher_id;
        $lesson->save();
    }

    return response()->json(['message' => 'تم تعديل الجدول الأسبوعي بنجاح.']);
}


//________________________________________________________________________________________
//تابع لحذف الجدول الاسبوعي

public function deleteWeeklySchedule(Request $request)
{
    $request->validate([
        'classroom_id' => 'required|exists:classrooms,id',
        'semester' => 'required|in:first,second',
    ]);

    $schedule = WeeklySchedule::where('classroom_id', $request->classroom_id)
                              ->where('semester', $request->semester)
                              ->first();

    if (!$schedule) {
        return response()->json(['message' => 'الجدول غير موجود.'], 404);
    }

    $schedule->lessons()->delete();

    $schedule->delete();

    return response()->json(['message' => 'تم حذف الجدول الأسبوعي بنجاح.'], 200);
}
//________________________________________________________________________________________


    public function getTeacherWeeklySchedule(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'المعلم غير موجود أو المستخدم غير معلم'], 404);
        }

        $teacher = $user->teacher;

        // جلب قيمة الفصل الدراسي من request
        $semester = $request->query('semester');

        // التحقق من صحة الفصل الدراسي
        if (!in_array($semester, ['first', 'second'])) {
            return response()->json(['message' => 'يجب تحديد الفصل الدراسي بشكل صحيح: first أو second'], 400);
        }

        // جلب الدروس بناءً على الفصل الدراسي
        $lessons = Lesson::with([
            'subject:id,name',
            'weeklySchedule.classroom.grade:id,name',
        ])
            ->where('teacher_id', $teacher->id)
            ->whereHas('weeklySchedule', function($query) use ($semester) {
                $query->where('semester', $semester);
            })
            ->orderBy('day')
            ->orderBy('time')
            ->get();

        if ($lessons->isEmpty()) {
            return response()->json([
                'teacher_id' => $teacher->id,
                'teacher_name' => $user->first_name . ' ' . $user->last_name,
                'semester' => $semester,
                'schedule' => [],
                'message' => 'لا يوجد جدول لهذا المعلم في هذا الفصل الدراسي.'
            ]);
        }

        // تجميع الدروس حسب اليوم
        $groupedLessons = $lessons->groupBy('day')->map(function ($dayLessons) {
            return $dayLessons->map(function ($lesson) {
                return [
                    'subject' => $lesson->subject->name ?? null,
                    'classroom' => $lesson->weeklySchedule->classroom->name ?? null,
                    'grade' => $lesson->weeklySchedule->classroom->grade->name ?? null,
                    'time' => $lesson->time,
                ];
            });
        });

        return response()->json([
            'teacher_id' => $teacher->id,
            'teacher_name' => $user->first_name . ' ' . $user->last_name,
            'semester' => $semester,
            'schedule' => $groupedLessons,
        ]);
    }



}

