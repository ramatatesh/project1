<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;


class NoteController extends Controller
{
      // تابع لإنشاء ملاحظة جديدة
    public function storeNote(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'content' => 'required|string|max:1000',
        ]);

        $student = Student::find($request->student_id);

        if (!$student) {
            return response()->json(['message' => 'الطالب غير موجود.'], 404);
        }

        // هنا التأكد أنك تستخدم فقط content وليس محتوى الطلب كاملاً
        $note = Note::create([
            'student_id' => $student->id,
            'content' => $request->input('content'), // ← هذا هو الأهم
        ]);

        return response()->json([
            'message' => 'تم إنشاء الملاحظة بنجاح',
            'note' => [
                'id' => $note->id,
                'student_id' => $note->student_id,
                'content' => $note->content,
                'created_at' => $note->created_at->format('Y-m-d'),
            ]
        ], 201);
    }



    //___________________________________________________________________
             //تابع لحذف الملاحظة
          public function destroy($id)
          {


              $note = Note::find($id);

              if (!$note) {
                  return response()->json(['message' => 'الملاحظة غير موجودة.'], 404);
              }


              $note->delete();

              return response()->json(['message' => 'تم حذف الملاحظة بنجاح']);
          }

          //___________________________________________________________________

          //تابع لتعديل الملاحظة
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'الملاحظة غير موجودة.'], 404);
        }

        // تحديث المحتوى مباشرة من input وليس body كامل
        $note->update([
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'message' => 'تم تعديل الملاحظة بنجاح',
            'note' => [
                'id' => $note->id,
                'student_id' => $note->student_id,
                'content' => $note->content,
                'updated_at' => $note->updated_at->format('Y-m-d'),
            ],
        ]);
    }

    //___________________________________________________________________

       //تابع لجلب جميع ملاحظات طالب معين
    public function allnoteStudent()
    {
        $student = auth()->user()->student;

        if (!$student) {
            return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
        }

        $notes = Note::where('student_id', $student->id)
            ->latest()
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'content' => $note->content,
                    'created_at' => $note->created_at->format('Y-m-d'), // ← التاريخ بصيغة مبسطة
                ];
            });

        return response()->json(['notes' => $notes]);
    }
//_____________________________________________________________________________________________________________

    public function getNoteStudent($studentId)
    {
        $student = \App\Models\Student::find($studentId);

        if (!$student) {
            return response()->json(['message' => 'الطالب غير موجود.'], 404);
        }

        $notes = \App\Models\Note::where('student_id', $student->id)
            ->latest()
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'content' => $note->content,
                    'created_at' => $note->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'student_id' => $studentId,
            'notes' => $notes,
        ]);
    }


}
