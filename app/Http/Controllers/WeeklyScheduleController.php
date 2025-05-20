<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WeeklyScheduleImport;
use App\Models\WeeklySchedule;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Student;

class WeeklyScheduleController extends Controller
{
// تابع لانشاء جدول اسبوعي
public function storeWeeklySchedule(Request $request)
{
    $validated = $request->validate([
        'grade' => 'required|string',
        'section' => 'required|string',
        'file' => 'required|file|mimes:xlsx,xls',
    ]);

    $grade = Grade::where('name', $validated['grade'])->first();
    if (!$grade) {
        return response()->json(['message' => 'الصف غير موجود.'], 404);
    }

    $classroom = Classroom::where('grade_id', $grade->id)
                        ->where('name', $validated['section'])
                        ->first();

    if (!$classroom) {
        return response()->json(['message' => 'الشعبة غير موجودة.'], 404);
    }

    Excel::import(new WeeklyScheduleImport($validated['grade'], $validated['section']), $request->file('file'));

    return response()->json(['message' => 'تم حفظ الجدول الأسبوعي بنجاح .'], 201);
}

//________________________________________________________________________________________

// تابع يعرض الجدول الأسبوعي بناءً على الصف والشعبة

public function getWeeklySchedule(Request $request)
{
    $student = Student::where('user_id', auth()->id())->first();

    $query = WeeklySchedule::where('grade_id', $student->grade_id)
        ->where('classroom_id', $student->classroom_id);


    if ($request->has('day')) {
        $query->where('day', $request->day);
    }

    $schedule = $query->get();

    return response()->json([
        'grade' => $student->grade,
        'section' => $student->section,
        'schedule' => $schedule,
    ]);
}

//________________________________________________________________________________________
}
