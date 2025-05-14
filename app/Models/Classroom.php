<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{

    protected $fillable = ['grade_id','name'];


    
public function students()
{
    return $this->hasMany(Student::class);
}

public function grade()
{
    return $this->belongsTo(Grade::class);
}

public function weeklySchedules()
{
    return $this->hasMany(WeeklySchedule::class);
}

}
