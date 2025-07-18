<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    // تابع لعرض المواد
    public function getSubject()
{
    $subjects = Subject::all();
    return response()->json($subjects);
}

//_____________________________________________________________________________
    public function getStudentSubjects()
    {
        $user = auth()->user();

        // التأكد أن المستخدم طالب
        if (!$user || !$user->student) {
            return response()->json(['message' => 'المستخدم ليس طالباً.'], 403);
        }

        $student = $user->student;
        $excludedSubjects = ['Sport', 'Arts', 'Music'];

        // جلب المواد المرتبطة بصف الطالب مع استثناء المواد
        $subjects = Subject::whereHas('grades', function ($query) use ($student) {
            $query->where('grades.id', $student->grade_id);
        })->whereNotIn('name', $excludedSubjects)
            ->get(['id', 'name']);

        return response()->json([
            'student_name' => $user->first_name . ' ' . $user->last_name,
            'grade_id' => $student->grade_id,
            'subjects' => $subjects,
        ]);
    }
//__________________________________________________________________________________________________

    public function getMySubjects()
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'المعلم غير موجود أو المستخدم غير معلم.'], 404);
        }

        // استخراج المواد التي يدرّسها هذا المعلم عبر الحصص المرتبطة به
        $subjectIds = Lesson::where('teacher_id', $user->teacher->id)
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)
            ->select('id', 'name')
            ->get();

        return response()->json([
            'teacher_id' => $user->teacher->id,
            'subjects' => $subjects
        ]);
    }


}
