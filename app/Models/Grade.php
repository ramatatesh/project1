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

    public function students()
    {
    return $this->hasMany(Student::class);
    }

    public function exam_schedule()
    {
    return $this->hasOne(Exam_schedule::class);
    }
    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    public function admins()
    {
    return $this->hasOne(Admin::class);
    }

}
