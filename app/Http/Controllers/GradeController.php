<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;

class GradeController extends Controller
{
    public function getGrades()
{

    $grades = Grade::all();

    return response()->json([ 'grades' => $grades], 200);
}

}
