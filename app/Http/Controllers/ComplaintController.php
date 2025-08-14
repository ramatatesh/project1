<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function addComplaint(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
        ]);

        $student = auth()->user()->student;

        if (!$student) {
            return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
        }

        $complaint = Complaint::create([
            'student_id' => $student->id,
            'description' => $request->description,
        ]);

        return response()->json(['message' => __('app.complaint'), 'complaint' => $complaint], 200);
    }
//_______________________________________________________________________________________________________________

    public function getComplaints()
    {
        $student = auth()->user()->student;

        if (!$student) {
            return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
        }

        $complaints = $student->complaints()
            ->latest()
            ->get()
            ->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'description' => $complaint->description,
                    'created_at' => $complaint->created_at->format('Y-m-d'),
                ];
            });

        return response()->json(['complaints' => $complaints], 200);
    }
//_________________________________________________________________________________________________

    public function updateComplaint(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string',
        ]);

        // جلب الطالب المسجل حالياً
        $student = auth()->user()->student;

        if (!$student) {
            return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
        }

        // جلب الشكوى الخاصة بالطالب فقط
        $complaint = $student->complaints()->where('id', $id)->first();

        if (!$complaint) {
            return response()->json(['message' => __('app.complaintNotFound')], 404);
        }

        // تعديل الشكوى
        $complaint->update([
            'description' => $request->description,
        ]);

        return response()->json(['message' => __('app.complaintUpdate'), 'complaint' => $complaint], 200);
    }
//___________________________________________________________________________________________________________

    public function deleteComplaint($id)
    {
        // جلب الطالب المسجل حالياً
        $student = auth()->user()->student;

        if (!$student) {
            return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
        }

        // جلب الشكوى الخاصة بالطالب فقط
        $complaint = $student->complaints()->where('id', $id)->first();

        if (!$complaint) {
            return response()->json(['message' => __('app.complaintNotFound')], 404);
        }

        // حذف الشكوى
        $complaint->delete();

        return response()->json(['message' => __('app.deleteComplaint')], 200);
    }
//_______________________________________________________________________________________________

    public function getComplaintsByGrade($gradeId)
    {
        // جلب جميع الشكاوى المقدمة من الطلاب الذين ينتمون إلى الصف المحدد
        $complaints = Complaint::whereHas('student', function ($query) use ($gradeId) {
            $query->where('grade_id', $gradeId);
        })
            ->with('student.user') // لجلب بيانات الطالب والمستخدم المرتبط
            ->latest()
            ->get()
            ->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'description' => $complaint->description,
                    'created_at' => $complaint->created_at->format('Y-m-d '),
                    'student' => [
                        'id' => $complaint->student->id,
                        'name' => $complaint->student->user->first_name . ' ' . $complaint->student->user->last_name,
                    ]
                ];
            });

        return response()->json(['complaints' => $complaints], 200);
    }

}
