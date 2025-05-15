<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['mother_name','gender','dob','user_id','birth_date','grade','classroom_id',
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
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function bill()
    {
        return $this->hasOne(Bill::class);
    }
}
