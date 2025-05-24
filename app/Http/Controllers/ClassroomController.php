<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\Classroom;

class ClassroomController extends Controller
{

public function getClassroomsByGrade($gradeName)
{
    $grade = Grade::where('name', $gradeName)->first();

    if (!$grade) {
        return response()->json([
            'message' => 'الصف غير موجود.',
        ], 404);
    }

    $classrooms = Classroom::where('grade_id', $grade->id)->get();

    return response()->json([
        'grade' => $gradeName,
        'classrooms' => $classrooms,
    ]);
}

//________________________________________________________________________________________

}
