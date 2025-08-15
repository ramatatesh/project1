<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function uploadFile(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => __('app.teacher')], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx,pptx,jpeg,png|max:10240',
            'grade_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $isTeachingSubject = Lesson::whereHas('weeklySchedule.classroom', function ($query) use ($request) {
            $query->where('grade_id', $request->grade_id);
        })
            ->where('teacher_id', $user->teacher->id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (!$isTeachingSubject) {
            return response()->json(['message' => __('app.teacherSubject')], 403);
        }

        $fileUploaded = $request->file('file');
        $path = $fileUploaded->store('files', 'public');

        $file = File::create([
            'teacher_id' => $user->teacher->id,
            'name' => $request->name,
            'path' => $path,
            'subject_id' => $request->subject_id,
        ]);

        $classroomIds = \App\Models\Lesson::whereHas('weeklySchedule.classroom', function ($query) use ($request) {
            $query->where('grade_id', $request->grade_id);
        })
            ->where('teacher_id', $user->teacher->id)
            ->where('subject_id', $request->subject_id)
            ->with('weeklySchedule.classroom')
            ->get()
            ->pluck('weeklySchedule.classroom.id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($classroomIds)) {
            return response()->json(['message' => __('app.teacherClassroom')], 404);
        }

        $file->classroom()->sync($classroomIds);

        return response()->json([
            'message' => __('app.file'),
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
            return response()->json(['message' => __('app.teacher')], 404);
        }

        $file = File::find($id);
        if (!$file || $file->teacher_id != $user->teacher->id) {
            return response()->json(['message' => __('app.fileTeacher')], 404);
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
            return response()->json(['message' => __('app.classroomTeacher')], 404);
        }

        $file->save();

        // تحديث الربط
        $file->classroom()->sync($classroomIds);

        return response()->json([
            'message' => __('app.updateFile'),
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
            return response()->json(['message' => __('app.teacher')], 404);
        }

        $file = File::find($id);

        if (!$file || $file->teacher_id != $user->teacher->id) {
            return response()->json(['message' => __('app.fileTeacher')], 404);
        }

        // إزالة العلاقة مع الشعب فقط (لا نحذف الملف من التخزين)
        $file->classroom()->detach();

        // حذف غير نهائي (Soft Delete)
        $file->delete();

        return response()->json(['message' => __('app.fileDe')], 200);
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
            ->get();

        return response()->json([
            'trashed_files' => $files
        ]);
    }
//______________________________________________________________________________________________

    public function listFiles()
    {
        $user = auth()->user();

        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'المعلم غير موجود أو المستخدم غير معلم'], 404);
        }

        $files = \App\Models\File::where('teacher_id', $user->teacher->id)
            ->with(['classroom.grade']) // نحصل على الشعب فقط للوصول للصفوف
            ->latest()
            ->get()
            ->map(function ($file) {
                // جلب الصفوف المرتبطة دون تكرار
                $grades = $file->classroom
                    ->pluck('grade')
                    ->filter()
                    ->unique('id')
                    ->map(function ($grade) {
                        return [
                            'id' => $grade->id,
                            'name' => $grade->name,
                        ];
                    })
                    ->values();

                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'uploaded_at' => $file->created_at->format('Y-m-d H:i'),
                    'file_url' => asset('storage/' . $file->path),
                    'grades' => $grades, // عرض الصفوف فقط
                ];
            });

        return response()->json([
            'files' => $files
        ]);
    }
//________________________________________________________________________________________

   public function getFilesBySubjectForStudent(Request $request)
{
    $user = auth()->user();

    if (!$user || !$user->student) {
        return response()->json(['message' => 'المستخدم غير طالب'], 403);
    }

    $request->validate([
        'subject_id' => 'required|exists:subjects,id',
    ]);

    $student = $user->student;
    $classroom = $student->classroom;

    if (!$classroom) {
        return response()->json(['message' => 'الطالب غير منسوب إلى شعبة'], 404);
    }

    // استخراج معرّفات المعلمين الذين يدرّسون هذا الطالب في هذه المادة
    $teacherIds = Lesson::whereHas('weeklySchedule', function ($query) use ($classroom) {
        $query->where('classroom_id', $classroom->id);
    })
        ->where('subject_id', $request->subject_id)
        ->pluck('teacher_id')
        ->unique();

    // جلب الملفات من هؤلاء المعلمين فقط، وللمادة المحددة فقط
    $files = File::whereIn('teacher_id', $teacherIds)
        ->where('subject_id', $request->subject_id) // ✅ الشرط المضاف
        ->whereHas('classroom', function ($query) use ($classroom) {
            $query->where('classroom_id', $classroom->id);
        })
        ->with(['teacher.user', 'classroom', 'classroom.grade'])
        ->latest()
        ->get()
        ->map(function ($file) {
            return [
                'file_id' => $file->id,
                'file_name' => $file->name,
                'file_url' => asset('storage/' . $file->path),
                'teacher' => optional($file->teacher->user)->first_name . ' ' . optional($file->teacher->user)->last_name,
                'uploaded_at' => $file->created_at->format('Y-m-d H:i'),
                'grades' => $file->classroom->pluck('grade.name')->unique()->values(),
            ];
        });

    return response()->json([
        'files' => $files
    ]);
}
//_______________________________________________________________________________________________

    public function getFilesByTeacherId($teacher_id)
    {
        // التأكد من وجود المعلم
        $teacher = Teacher::find($teacher_id);

        if (!$teacher) {
            return response()->json(['message' => 'المعلم غير موجود'], 404);
        }

        // جلب الملفات المرتبطة بالمعلم
        $files = File::where('teacher_id', $teacher->id)
            ->with('classroom.grade') // لجلب الصفوف المرتبطة عبر الشعب
            ->latest()
            ->get()
            ->map(function ($file) {
                return [
                    'file_id' => $file->id,
                    'file_name' => $file->name,
                    'file_url' => asset('storage/' . $file->path),
                    'uploaded_at' => $file->created_at->format('Y-m-d H:i'),
                    'grades' => $file->classroom
                        ->pluck('grade.name')
                        ->unique()
                        ->values(),
                ];
            });

        return response()->json([
            'teacher_id' => $teacher->id,
            'files' => $files,
        ]);
    }



}
