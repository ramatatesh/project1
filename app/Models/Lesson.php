<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['weekly_schedule_id', 'subject_id', 'teacher_id', 'day', 'time'];

    public function weeklySchedule()
    {
        return $this->belongsTo(WeeklySchedule::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
