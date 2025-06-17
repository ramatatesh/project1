<?php

namespace App\Http\Controllers;

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
        'semester' => 'required|string',
        'lessons' => 'required|array|max:35',
        'lessons.*.subject_id' => 'required|exists:subjects,id',
        'lessons.*.teacher_id' => 'required|exists:teachers,id',
    ]);


    $existingSchedule = WeeklySchedule::where('classroom_id', $request->classroom_id)
                                      ->where('semester', $request->semester)
                                      ->first();

    if ($existingSchedule) {
        return response()->json([
            'message' => 'يوجد بالفعل جدول أسبوعي لهذه الشعبة في هذا الفصل.',
        ], 409);
    }


    $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
    $times = ['08:00', '08:45', '09:45', '10:30', '11:15', '12:15', '13:00'];


    $schedule = WeeklySchedule::create([
        'classroom_id' => $request->classroom_id,
        'semester' => $request->semester,
    ]);


    $lessonIndex = 0;
    foreach ($days as $day) {
        foreach ($times as $time) {
            if (!isset($request->lessons[$lessonIndex])) {
                break 2;
            }

            $lessonData = $request->lessons[$lessonIndex];

            Lesson::create([
                'weekly_schedule_id' => $schedule->id,
                'subject_id' => $lessonData['subject_id'],
                'teacher_id' => $lessonData['teacher_id'],
                'day' => $day,
                'time' => $time,
            ]);

            $lessonIndex++;
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

    $schedules = WeeklySchedule::where('classroom_id', $classroom->id)
        ->with(['lessons.subject:id,name', 'lessons.teacher.user:id,username'])
        ->orderBy('semester')
        ->get();

    if ($schedules->isEmpty()) {
        return response()->json(['message' => 'لا يوجد جداول أسبوعية لهذه الشعبة.'], 404);
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
        'semester' => 'required|string',
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
        'semester' => 'required|string',
        'lessons' => 'sometimes|array',
        'lessons.*.id' => 'required|exists:lessons,id',
        'lessons.*.subject_id' => 'sometimes|exists:subjects,id',
        'lessons.*.teacher_id' => 'sometimes|exists:teachers,id',
        'lessons.*.day' => 'sometimes|string',
        'lessons.*.time' => 'sometimes|string',
    ]);

    $schedule = WeeklySchedule::where('classroom_id', $request->classroom_id)
        ->where('semester', $request->semester)
        ->first();

    if (!$schedule) {
        return response()->json(['message' => 'الجدول غير موجود'], 404);
    }


    if ($request->has('lessons')) {
        foreach ($request->lessons as $lessonData) {
            $lesson = Lesson::find($lessonData['id']);
            if (!$lesson || $lesson->weekly_schedule_id != $schedule->id) {
                continue;
            }

            $lesson->subject_id = $lessonData['subject_id'] ?? $lesson->subject_id;
            $lesson->teacher_id = $lessonData['teacher_id'] ?? $lesson->teacher_id;
            $lesson->day = $lessonData['day'] ?? $lesson->day;
            $lesson->time = $lessonData['time'] ?? $lesson->time;
            $lesson->save();
        }
    }

    return response()->json(['message' => 'تم تعديل الجدول الأسبوعي بنجاح.']);
}


//________________________________________________________________________________________
//تابع لحذف الجدول الاسبوعي

public function deleteWeeklySchedule(Request $request)
{
    $request->validate([
        'classroom_id' => 'required|exists:classrooms,id',
        'semester' => 'required|string',
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

}

