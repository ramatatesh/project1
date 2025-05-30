<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{

    protected $fillable = ['subject_id','exam_schedule_id','day','time'];

   public function examSchedule()
    {
        return $this->belongsTo(ExamSchedule::class);
    }
}
