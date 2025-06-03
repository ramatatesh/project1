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

 // تابع يعرض الجدول الأسبوعي بناءً على الصف والشعبة
public function getWeeklySchedule(Request $request)
{
    // الطالب الحالي من التوكن
    $student = auth()->user()->student;

    if (!$student) {
        return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
    }

    // جلب الشعبة (classroom) المرتبط فيها الطالب
    $classroom = $student->classroom;

    if (!$classroom) {
        return response()->json(['message' => 'الطالب غير مرتبط بأي شعبة.'], 404);
    }

    // جلب الجدول الأسبوعي المرتبط بهي الشعبة
    $schedule = WeeklySchedule::where('classroom_id', $classroom->id)
        ->with([
            'lessons.subject:id,name',
            'lessons.teacher.user:id,username'
        ])
        ->first();

    if (!$schedule) {
        return response()->json(['message' => 'لا يوجد جدول أسبوعي لهذه الشعبة.'], 404);
    }

    // تنظيم الدروس حسب الأيام
    $grouped = $schedule->lessons->groupBy('day')->map(function ($lessons) {
        return $lessons->map(function ($lesson) {
            return [
                'time' => $lesson->time,
                'subject' => $lesson->subject->name ?? 'غير معروف',
                'teacher' => $lesson->teacher->user->username ?? 'غير معروف',
            ];
        })->sortBy('time')->values();
    });

    return response()->json([
        'student' => $student->user->username,
        'classroom' => $classroom->name,
        'grade' => $classroom->grade->name,
        'semester' => $schedule->semester,
        'weekly_schedule' => $grouped,
    ]);
}

//________________________________________________________________________________________

}

