<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\Classroom;

class ClassroomController extends Controller
{

    public function getClassroomsByGrade($gradeId)
    {
        $grade = Grade::find($gradeId);

        if (!$grade) {
            return response()->json([
                'message' => 'الصف غير موجود.',
            ], 404);
        }

        $classrooms = Classroom::where('grade_id', $grade->id)->get();

        return response()->json([
            'grade' => $grade->name,
            'classrooms' => $classrooms,
        ]);
    }

//________________________________________________________________________________________

}
