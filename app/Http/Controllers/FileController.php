<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
//________________________________________________________________________________________________________

    public function updateFile(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'المعلم غير موجود أو المستخدم غير معلم'], 404);
        }

        // التحقق من وجود الملف
        $file = File::find($id);
        if (!$file || $file->teacher_id != $user->teacher->id) {
            return response()->json(['message' => 'الملف غير موجود أو لا يتبع لهذا المعلم.'], 404);
        }

        // التحقق من البيانات
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'file' => 'sometimes|file|mimes:pdf,doc,docx,xlsx,pptx,jpeg,png|max:10240',
            'grade_id' => 'required|exists:grades,id',
        ]);

        // تحديث الاسم إن وجد
        if ($request->filled('name')) {
            $file->name = $request->name;
        }

        // تحديث الملف إن وجد
        if ($request->hasFile('file')) {
            // حذف الملف القديم (اختياري)
            \Storage::disk('public')->delete($file->path);

            // رفع الجديد
            $uploaded = $request->file('file');
            $path = $uploaded->store('files', 'public');
            $file->path = $path;
        }

        // تحديث الصف وإعادة ربط الشعب التي يدرّسها المعلم فقط
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

        $file->save();

        // تحديث الربط
        $file->classroom()->sync($classroomIds);

        return response()->json([
            'message' => 'تم تعديل الملف وربطه بالشعب الجديدة بنجاح.',
            'file' => $file,
            'file_url' => asset('storage/' . $file->path),
            'classroom_ids' => $classroomIds,
        ]);
    }
//_______________________________________________________________________________________________

    public function deleteFile($id)
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'المعلم غير موجود أو المستخدم غير معلم'], 404);
        }

        $file = File::find($id);

        if (!$file || $file->teacher_id != $user->teacher->id) {
            return response()->json(['message' => 'الملف غير موجود أو لا يخص هذا المعلم'], 404);
        }

        // إزالة العلاقة مع الشعب فقط (لا نحذف الملف من التخزين)
        $file->classroom()->detach();

        // حذف غير نهائي (Soft Delete)
        $file->delete();

        return response()->json(['message' => 'تم نقل الملف إلى سلة المحذوفات.'], 200);
    }
//____________________________________________________________________________________________

    public function restoreFile($id)
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'المعلم غير موجود أو المستخدم غير معلم'], 404);
        }

        $file = File::withTrashed()->find($id);

        if (!$file || $file->teacher_id != $user->teacher->id) {
            return response()->json(['message' => 'الملف غير موجود أو لا يخص هذا المعلم'], 404);
        }

        $file->restore();

        return response()->json(['message' => 'تم استرجاع الملف بنجاح.']);
    }
//________________________________________________________________________________________

    public function forceDeleteFile($id)
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'المعلم غير موجود أو المستخدم غير معلم'], 404);
        }

        $file = File::withTrashed()->find($id);

        if (!$file || $file->teacher_id != $user->teacher->id) {
            return response()->json(['message' => 'الملف غير موجود أو لا يخص هذا المعلم'], 404);
        }

        Storage::disk('public')->delete($file->path);
        $file->classroom()->detach();
        $file->forceDelete();

        return response()->json(['message' => 'تم حذف الملف نهائيًا.']);
    }
//____________________________________________________________________________________________

    public function trashedFiles()
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'المعلم غير موجود أو المستخدم غير معلم'], 404);
        }

        $files = File::onlyTrashed()
            ->where('teacher_id', $user->teacher->id)
            ->with('classroom')
            ->get();

        return response()->json([
            'trashed_files' => $files
        ]);
    }


}
