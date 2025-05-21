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
        'username' => 'required|string|max:255',
        'father_name' => 'required|string|max:255',
        'day' => 'required|string',
        'content' => 'required|string|max:1000',
    ]);


    $user = User::where('username', $request->username)
                ->where('father_name', $request->father_name)
                ->first();


    if (!$user) {
        return response()->json(['message' => 'المستخدم غير موجود.'], 404);
    }


    $student = Student::where('user_id', $user->id)->first();


    if (!$student) {
        return response()->json(['message' => 'الطالب غير موجود.'], 404);
    }

    $note = Note::create([
        'student_id' => $student->id,
        'content' => $request->content,
        'day' => $request->day,
    ]);

    return response()->json(['message' => 'تم إنشاء الملاحظة بنجاح', 'note' => $note], 201);
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


          $note->update([
              'content' => $request->content,
          ]);

          return response()->json(['message' => 'تم تعديل الملاحظة بنجاح', 'note' => $note]);
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
              ->get();

          return response()->json($notes);
      }

 //___________________________________________________________________

}
