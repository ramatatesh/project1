<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        'lessons' => 'required|array',
        'lessons.*.subject_id' => 'required|exists:subjects,id',
        'lessons.*.teacher_id' => 'required|exists:teachers,id',
        'lessons.*.day' => 'required|string',
        'lessons.*.time' => 'required|string',
    ]);

    //  التحقق من وجود جدول سابق لنفس الشعبة والفصل
    $existingSchedule = WeeklySchedule::where('classroom_id', $request->classroom_id)
                                      ->where('semester', $request->semester)
                                      ->first();

    if ($existingSchedule) {
        return response()->json([
            'message' => 'يوجد بالفعل جدول أسبوعي لهذه الشعبة في هذا الفصل.',
        ], 409);
    }

    //  التحقق من التكرار في اليوم والوقت ضمن نفس الطلب
    $usedSlots = [];

    foreach ($request->lessons as $lesson) {
        $slotKey = $lesson['day'] . '_' . $lesson['time'];
        if (isset($usedSlots[$slotKey])) {
            return response()->json([
                'message' => "لا يمكن تكرار نفس اليوم والوقت: {$lesson['day']} - {$lesson['time']}",
            ], 422);
        }
        $usedSlots[$slotKey] = true;
    }

    // إنشاء الجدول
    $schedule = WeeklySchedule::create([
        'classroom_id' => $request->classroom_id,
        'semester' => $request->semester,
    ]);

    foreach ($request->lessons as $lesson) {
        Lesson::create([
            'weekly_schedule_id' => $schedule->id,
            'subject_id' => $lesson['subject_id'],
            'teacher_id' => $lesson['teacher_id'],
            'day' => $lesson['day'],
            'time' => $lesson['time'],
        ]);
    }

    return response()->json(['message' => 'تم إنشاء الجدول الأسبوعي بنجاح.'], 201);
}



//________________________________________________________________________________________

 // تابع يعرض الجدول الأسبوعي بناءً على توكن الطالب (للطالب)
public function getWeeklySchedule(Request $request)
{
    // الطالب الحالي من التوكن
    $student = auth()->user()->student;

    if (!$student) {
        return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
    }

    // جلب الشعبة المرتبط فيها الطالب
    $classroom = $student->classroom;

    if (!$classroom) {
        return response()->json(['message' => 'الطالب غير مرتبط بأي شعبة.'], 404);
    }

    // جلب جميع الجداول المرتبطة بهي الشعبة
    $schedules = WeeklySchedule::where('classroom_id', $classroom->id)
        ->with(['lessons.subject:id,name', 'lessons.teacher.user:id,username'])
        ->orderBy('semester') // ممكن تعدلي الترتيب حسب الحاجة
        ->get();

    if ($schedules->isEmpty()) {
        return response()->json(['message' => 'لا يوجد جداول أسبوعية لهذه الشعبة.'], 404);
    }

    // تنظيم كل جدول حسب الأيام
    $result = $schedules->map(function ($schedule) {
        $grouped = $schedule->lessons->groupBy('day')->map(function ($lessons) {
            return $lessons->map(function ($lesson) {
                return [
                    'time' => $lesson->time,
                    'subject' => $lesson->subject->name ?? 'غير معروف',
                    'teacher' => $lesson->teacher->user->username ?? 'غير معروف',
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

    // نجيب الجدول باستخدام الشعبة والفصل
    $schedule = WeeklySchedule::where('classroom_id', $request->classroom_id)
        ->where('semester', $request->semester)
        ->first();

    if (!$schedule) {
        return response()->json(['message' => 'الجدول غير موجود'], 404);
    }

    // تعديل الدروس
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

