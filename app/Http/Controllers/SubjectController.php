<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    // تابع لعرض المواد
    public function getSubject()
{
    $subjects = Subject::all();
    return response()->json($subjects);
}

//_____________________________________________________________________________
}
