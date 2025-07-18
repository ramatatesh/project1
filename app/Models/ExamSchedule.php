<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['grade_id','semester'];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
