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
      public function store(Request $request)
      {
          $request->validate([
              'student_id' => 'required|exists:students,id',
              'content' => 'required|string|max:1000',
          ]);

          $user = auth()->user();

          // تأكد أن المستخدم الحالي مشرف
          if ($user->role !== 'admain') {
              return response()->json(['message' => 'فقط المشرفين يمكنهم إرسال ملاحظات.'], 403);
          }

          // جيب سجل المشرف
          $admain = $user->admain;

          if (!$admain) {
              return response()->json(['message' => 'حدث خطأ أثناء جلب بيانات المشرف.'], 500);
          }

          $note = Note::create([
              'student_id' => $request->student_id,
              'admain_id' => $admain->id,
              'content' => $request->content,
          ]);

          return response()->json(['message' => 'تم إنشاء الملاحظة بنجاح', 'note' => $note], 201);
      }


          //___________________________________________________________________
             //تابع لحذف الملاحظة
          public function destroy($id)
          {
              $admain = auth()->user()->admain;
              if (!$admain) {
                  return response()->json(['message' => 'فقط المشرفين يمكنهم حذف الملاحظات.'], 403);
              }

              $note = Note::find($id);

              if (!$note) {
                  return response()->json(['message' => 'الملاحظة غير موجودة.'], 404);
              }

              if ($note->admain_id !== $admain->id) {
                  return response()->json(['message' => 'غير مصرح لك بحذف هذه الملاحظة.'], 403);
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

          $admain = auth()->user()->admain;
          if (!$admain) {
              return response()->json(['message' => 'فقط المشرفين يمكنهم تعديل الملاحظات.'], 403);
          }

          $note = Note::find($id);

          if (!$note) {
              return response()->json(['message' => 'الملاحظة غير موجودة.'], 404);
          }

          if ($note->admain_id !== $admain->id) {
              return response()->json(['message' => 'غير مصرح لك بتعديل هذه الملاحظة.'], 403);
          }

          $note->update([
              'content' => $request->content,
          ]);

          return response()->json(['message' => 'تم تعديل الملاحظة بنجاح', 'note' => $note]);
      }

          //___________________________________________________________________



       //___________________________________________________________________

       //تابع لجلب جميع ملاحظات طالب معين
      public function allnoteStudent()
      {
          // جلب الطالب الذي ينتمي للمستخدم المصادق
          $student = auth()->user()->student;

          if (!$student) {
              return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
          }

          // جلب ملاحظات الطالب بناءً على الـ student_id
          $notes = Note::where('student_id', $student->id)
              ->with('admain.user:id,username') // جيب اسم المشرف يلي كتب الملاحظة
              ->latest()
              ->get();

          return response()->json($notes);
      }
}
