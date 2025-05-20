<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{

    protected $fillable = ['name'];

    public function classrooms()
{
    return $this->hasMany(Classroom::class);
}
    public function exam_schedule()
    {
        return $this->hasOne(Exam_schedule::class);
    }
}
