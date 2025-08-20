<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function storeNews(Request $request)
    {
        $request->validate([
            'grade_id' => 'required|integer|exists:grades,id',
            'title' => 'required|string|max:1000',
            'content' => 'required|string'
        ]);

        $news = News::create([
            'grade_id' => $request->grade_id,
            'title' => $request->title,
            'content' => $request->input('content'),
        ]);

        return response()->json(['message' => 'تم إنشاء الخبر بنجاح', 'news' => $news], 200);
    }
//_____________________________________________________________________________________________________
public function updateNews(Request $request, $id){
        $request->validate([
            'title' => 'nullable|string|max:1000',
            ' content' => 'nullable|string'
        ]);
        $news = News::find($id);

    if (!$news) {
        return response()->json(['message' => 'الخبر غير موجودة.'], 404);
    }
    $news->update([
        'title' => $request->title,
        'content' => $request->input('content'),
    ]);
    return response()->json(['message' => 'تم تعديل الخبر بنجاح', 'news' => $news]);

}
//__________________________________________________________________________________________
    public function deleteNews( $id){

        $news=  News::find($id);
    if (!$news) {
        return response()->json(['message' => 'الخبر غير موجود.'], 404);
    }

    $news->delete();

    return response()->json(['message' => 'تم حذف الخبر بنجاح']);
    }
//_______________________________________________________________________________________
    public function getNews()
    {
        $student = auth()->user()->student;

        if (!$student) {
            return response()->json(['message' => 'المستخدم ليس طالبًا.'], 403);
        }

        // جلب الأخبار التابعة لنفس الصف الذي ينتمي إليه الطالب
        $news = News::where('grade_id', $student->grade_id)
            ->latest()
            ->get()
            ->map(function ($news) {
            return [
            'id' => $news->id,
            'content' => $news->content,
                'title' => $news->title,
            'created_at' => $news->created_at->format('Y-m-d'), // ← التاريخ بصيغة مبسطة
        ];
    });

        return response()->json($news);
    }
//____________________________________________________________________________________________________

    public function getAllNews()
    {
        $news = News::latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'grade_id' =>$item->grade_id,
                'title' => $item->title,
                'content' => $item->content,
                'created_at' => $item->created_at->format('Y-m-d'),
            ];
        });

        return response()->json(['news' => $news]);
    }

}
