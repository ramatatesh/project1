<?php

namespace App\Http\Controllers;

use App\Models\Information;
use Illuminate\Http\Request;

class InformationController extends Controller
{
    public function storeInfo(Request $request)
    {
        $request->validate([
            'about' => 'required|string|max:1000',
            'name' =>'required|string|max:255',
            'phone' => 'required|string|max:10',
            'address' => 'required|string|max:255',
        ]);

        $info = Information::create([
            'about'=>$request->about,
            'name' =>$request->name,
            'phone' =>$request->phone,
            'address' =>$request->address
        ]);
        return response()->json( ['تم إنشاء المعلومات بنجاح',$info, 200]);
    }
    //________________________________________________________________________________
    public function getInfo(){
        $info = Information::all();
        return response()->json([ 'info' => $info], 200);
    }
    //____________________________________________________________________________________
    public function updateInfo(Request $request)
    {
        $request->validate([
            'about' => 'nullable|string|max:1000',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
        ]);

        $info = Information::first();

        if (!$info) {
            return response()->json(['message' => 'لم يتم العثور على المعلومات لتحديثها.'], 404);
        }

        $info->update([
            'about' => $request->about,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address
        ]);

        return response()->json(['message' => 'تم تعديل المعلومات بنجاح', 'info' => $info], 200);
    }

    //_______________________________________________________________________________
}
