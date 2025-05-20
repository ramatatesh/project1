<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    protected $fillable = ['gender','user_id','specialization','grade','start_date','subject_id'];
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
}
