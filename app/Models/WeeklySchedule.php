<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{

    protected $fillable = ['grade_id','classroom_id','day','lesson_1','lesson_2',
        'lesson_3','lesson_4','lesson_5','lesson_6','lesson_7',];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
