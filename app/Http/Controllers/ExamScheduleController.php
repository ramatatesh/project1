<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSchedule;
use Illuminate\Http\Request;

class ExamScheduleController extends Controller
{
    public function storeExamSchedule(Request $request)
    {
        $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'semester' => 'required|in:first,second',
            'exams' => 'required|array|max:35',
            'exams.*.day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday',
            'exams.*.subject_id' => 'required|exists:subjects,id',
            'exams.*.time' => 'required|date_format:H:i',
            'exams.*.date' => 'required|date|after_or_equal:today', // ✅ تحقق من وجود التاريخ وصحته
        ]);

        // التحقق من وجود جدول مسبقًا
        $existingSchedule = ExamSchedule::where('grade_id', $request->grade_id)
            ->where('semester', $request->semester)
            ->first();

        if ($existingSchedule) {
            return response()->json([
                'message' => 'يوجد بالفعل جدول امتحاني لهذا الصف في هذا الفصل.',
            ], 409);
        }

        // إنشاء جدول الامتحانات
        $schedule = ExamSchedule::create([
            'grade_id' => $request->grade_id,
            'semester' => $request->semester,
        ]);

        // إدراج الامتحانات المرتبطة بالجدول
        foreach ($request->exams as $exam) {
            Exam::create([
                'exam_schedule_id' => $schedule->id,
                'subject_id' => $exam['subject_id'],
                'day' => $exam['day'],
                'time' => $exam['time'],
                'date' => $exam['date'],
            ]);
        }

        return response()->json(['message' => 'تم إنشاء جدول الامتحانات بنجاح.'], 201);
    }
//________________________________________________________________________________________________-

    public function getStudentExamSchedule(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->student) {
            return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
        }

        $student = $user->student;

        // جلب أحدث جدول امتحاني لهذا الصف
        $schedule = ExamSchedule::where('grade_id', $student->grade_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$schedule) {
            return response()->json(['message' => 'لا يوجد جدول امتحانات لهذا الصف.'], 404);
        }

        // جلب الامتحانات المرتبطة بالجدول
        $exams = $schedule->exams()
            ->with('subject:id,name')
            ->orderBy('day')
            ->orderBy('time')
            ->get();

        // تحويل البيانات لتنسيق واضح
        $formattedExams = $exams->map(function ($exam) {
            return [
                'subject' => $exam->subject->name ?? null,
                'day' => $exam->day,
                'date' => $exam->date, // ✅ عرض التاريخ
                'time' => $exam->time,
            ];
        });

        return response()->json([
            'student_name' => $user->first_name . ' ' . $user->last_name,
            'grade_id' => $student->grade_id,
            'semester' => $schedule->semester,
            'exams' => $formattedExams,
        ]);
    }

//________________________________________________________________________________________________

    public function getExamScheduleByGrade(Request $request)
    {
        $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'semester' => 'required|in:first,second',
        ]);

        $gradeId = $request->grade_id;
        $semester = $request->semester;

        $schedule = ExamSchedule::where('grade_id', $gradeId)
            ->where('semester', $semester)
            ->first();

        if (!$schedule) {
            return response()->json(['message' => 'لا يوجد جدول امتحانات لهذا الصف في هذا الفصل.'], 404);
        }

        $exams = $schedule->exams()->with('subject:id,name')->orderBy('day')->orderBy('time')->get();

        $formattedExams = $exams->map(function ($exam) {
            return [
                'subject' => $exam->subject->name ?? null,
                'day' => $exam->day,
                'time' => $exam->time,
            ];
        });

        return response()->json([
            'grade_id' => $gradeId,
            'semester' => $semester,
            'exams' => $formattedExams,
        ]);
    }
//----------------------------------------------------------------------------------

    public function updateExamSchedule(Request $request)
    {
        $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'semester' => 'required|in:first,second',
            'exams' => 'required|array',
            'exams.*.day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday',
            'exams.*.time' => 'required|date_format:H:i',
            'exams.*.subject_id' => 'sometimes|exists:subjects,id',
        ]);

        $schedule = ExamSchedule::where('grade_id', $request->grade_id)
            ->where('semester', $request->semester)
            ->first();

        if (!$schedule) {
            return response()->json(['message' => 'الجدول الامتحاني غير موجود.'], 404);
        }

        foreach ($request->exams as $examData) {
            $exam = Exam::where('exam_schedule_id', $schedule->id)
                ->where('day', $examData['day'])
                ->where('time', $examData['time'])
                ->first();

            if (!$exam) {
                continue;
            }

            $exam->subject_id = $examData['subject_id'] ?? $exam->subject_id;
            $exam->save();
        }

        return response()->json(['message' => 'تم تعديل الجدول الامتحاني بنجاح.']);
    }
//______________________________________________________________________________________

    public function deleteExamSchedule(Request $request)
    {
        $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'semester' => 'required|in:first,second',
        ]);

        $schedule = ExamSchedule::where('grade_id', $request->grade_id)
            ->where('semester', $request->semester)
            ->first();

        if (!$schedule) {
            return response()->json(['message' => 'جدول الامتحانات غير موجود.'], 404);
        }

        $schedule->delete(); // سيتم حذف جميع الامتحانات المرتبطة بفضل cascadeOnDelete

        return response()->json(['message' => 'تم حذف جدول الامتحانات بنجاح.']);
    }


}
