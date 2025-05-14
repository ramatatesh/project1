<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['mother_name','gender','dob','user_id','birth_date','grade_id','classroom_id',
    'section'];
    protected $table = 'students';
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
    return $this->belongsTo(Classroom::class);
    }

    public function grade()
    {
    return $this->belongsTo(Grade::class);
    }

}
