<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function uploadFile(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'المعلم غير موجود أو المستخدم غير معلم'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx,pptx,jpeg,png|max:10240', // 10MB max
            'grade_id' => 'required|exists:grades,id', // الصف إلزامي هنا
        ]);

        $fileUploaded = $request->file('file');
        $path = $fileUploaded->store('files', 'public');

        // حفظ الملف
        $file = File::create([
            'teacher_id' => $user->teacher->id,
            'name' => $request->name,
            'path' => $path,
        ]);

        // جلب كل الشعب داخل الصف المحدد والتي يُدرّسها هذا المعلم
        $classroomIds = \App\Models\Lesson::whereHas('weeklySchedule.classroom', function ($query) use ($request) {
            $query->where('grade_id', $request->grade_id);
        })
            ->where('teacher_id', $user->teacher->id)
            ->with('weeklySchedule.classroom')
            ->get()
            ->pluck('weeklySchedule.classroom.id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($classroomIds)) {
            return response()->json(['message' => 'لا توجد شعب في هذا الصف يُدرّسها المعلم.'], 404);
        }

        // ربط الملف مع هذه الشعب فقط
        $file->classroom()->sync($classroomIds);

        return response()->json([
            'message' => 'تم رفع الملف وربطه تلقائيًا بالشعب التي يدرسها المعلم في هذا الصف.',
            'file' => $file,
            'classroom_ids' => $classroomIds,
            'file_url' => asset('storage/' . $path),
        ], 201);
    }



}
