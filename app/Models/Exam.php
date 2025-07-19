<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{

    protected $fillable = ['subject_id','exam_schedule_id','day','time','date'];

   public function examSchedule()
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
