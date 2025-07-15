<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','subject_name','lesson_id','specialization','start_date'];
    protected $table = 'teachers';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class);
    }
     public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
