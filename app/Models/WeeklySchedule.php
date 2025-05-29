<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{

   protected $fillable = ['classroom_id', 'semester'];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
