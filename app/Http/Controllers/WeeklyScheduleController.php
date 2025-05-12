<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeeklySchedule;

class WeeklyScheduleController extends Controller
{
    // تابع لادخال الجدول الاسبوعي
    public function storeWeeklySchedule(Request $request)
{
    $validated = $request->validate([
        'grade' => 'required|string',
        'section' => 'required|string',
        'schedule' => 'required|array|min:1',
        'schedule.*.day' => 'required|string',
        'schedule.*.lesson_1' => 'required|string',
        'schedule.*.lesson_2' => 'required|string',
        'schedule.*.lesson_3' => 'required|string',
        'schedule.*.lesson_4' => 'required|string',
        'schedule.*.lesson_5' => 'required|string',
        'schedule.*.lesson_6' => 'required|string',
        'schedule.*.lesson_7' => 'nullable|string',
    ]);

    foreach ($validated['schedule'] as $daySchedule) {
        WeeklySchedule::create([
            'grade' => $validated['grade'],
            'section' => $validated['section'],
            'day' => $daySchedule['day'],
            'lesson_1' => $daySchedule['lesson_1'],
            'lesson_2' => $daySchedule['lesson_2'],
            'lesson_3' => $daySchedule['lesson_3'],
            'lesson_4' => $daySchedule['lesson_4'],
            'lesson_5' => $daySchedule['lesson_5'],
            'lesson_6' => $daySchedule['lesson_6'],
            'lesson_7' => $daySchedule['lesson_7'] ?? null,
        ]);
    }

    return response()->json([
        'message' => 'تم حفظ الجدول الأسبوعي بنجاح.'
    ], 201);
}
//________________________________________________________________________________________

    // تابع يعرض الجدول الأسبوعي بناءً على الصف والشعبة
    public function getWeeklySchedule(Request $request)
    {

        $validated = $request->validate([
            'grade' => 'required|string',
            'section' => 'required|string',
        ]);


        $schedule = WeeklySchedule::where('grade', $validated['grade'])
            ->where('section', $validated['section'])
            ->get();


        if ($schedule->isEmpty()) {
            return response()->json([
                'message' => 'لا يوجد جدول أسبوعي لهذا الصف والشعبة.',
            ], 404);
        }


        return response()->json([
            'grade' => $validated['grade'],
            'section' => $validated['section'],
            'schedule' => $schedule,
        ]);
    }

//________________________________________________________________________________________
}
