<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{

    protected $fillable = [
        'grade',
        'section',
        'day',
        'lesson_1',
        'lesson_2',
        'lesson_3',
        'lesson_4',
        'lesson_5',
        'lesson_6',
        'lesson_7',
    ];
}
