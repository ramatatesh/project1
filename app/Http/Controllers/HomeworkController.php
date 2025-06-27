<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Homework;
use App\Models\User;
use App\Models\Student;

class HomeworkController extends Controller
{
    //تابع انشاء واجب لشعبة معينة
    public function addHomework(Request $request)
{
    $request->validate([
        'classroom_id' => 'required|exists:classrooms,id',
        'subject_id' => 'required|exists:subjects,id',
        'content' => 'required|string',

    ]);

    $homework = Homework::create([
        'classroom_id' => $request->classroom_id,
        'subject_id' => $request->subject_id,
        'content' => $request->content,

    ]);

    return response()->json([
        'message' => 'تمت إضافة الواجب بنجاح.',
        'homework' => $homework,
    ], 201);
}
//________________________________________________________________________________
// تابع لتعديل واجب
public function updateHomework(Request $request, $id)
{
    $request->validate([
        'content' => 'required|string',
    ]);

    $homework = Homework::find($id);

    if (!$homework) {
        return response()->json(['message' => 'الواجب غير موجود'], 404);
    }

    $homework->update([
        'content' => $request->content,
    ]);

    return response()->json(['message' => 'تم تعديل الواجب بنجاح', 'homework' => $homework]);

}
//________________________________________________________________________________
// تابع لحذف واجب
public function deleteHomework($id)
{
    $homework = Homework::find($id);

    if (!$homework) {
        return response()->json(['message' => 'الواجب غير موجود'], 404);
    }

    $homework->delete();

    return response()->json(['message' => 'تم حذف الواجب بنجاح']);
}

//________________________________________________________________________________

//(للويب) تابع لعرض كل الواجبات حسب الشعبة والمادة
public function getHomeworksByClassroomAndSubject($classroom_id, $subject_id)
{

    $homeworks = Homework::where('classroom_id', $classroom_id)
                         ->where('subject_id', $subject_id)
                         ->get();

    return response()->json([
        'homeworks' => $homeworks
    ]);
}
//________________________________________________________________________________
// تابع لعرض الواجبات الخاصة بطالب حسب التوكن
public function getStudentHomeworks()
{
    $student = auth()->user()->student;

    if (!$student) {
        return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
    }

    $classroomId = $student->classroom_id;

    $homeworks = Homework::with('subject')
        ->where('classroom_id', $classroomId)
        ->get();

    return response()->json(['homeworks' => $homeworks]);
}
//________________________________________________________________________________
}
