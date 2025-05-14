<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\Classroom;

class ClassroomController extends Controller
{
    public function getClassroomsByGrade(Request $request)
{

    $validated = $request->validate(['grade' => 'required|string']
    , [
        'grade.required' => 'الرجاء إدخال اسم الصف.',
    ]);


    $grade = Grade::where('name', $validated['grade'])->first();

    if (!$grade) {
        return response()->json([
            'message' => 'الصف غير موجود.',
        ], 404);
    }


    $classrooms = Classroom::where('grade_id', $grade->id)->get();

    return response()->json(['grade' => $validated['grade'], 'classrooms' => $classrooms ]);
}

//________________________________________________________________________________________

}
