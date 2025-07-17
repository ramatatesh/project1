<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name','full_mark','min_mark'];

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

     public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function teachers()
    {
    return $this->hasMany(Teacher::class);
    }

    public function homeworks()
    {
    return $this->hasMany(Homework::class);
    }


    public function grades()
    {
        return $this->belongsToMany(Grade::class);
    }


}
