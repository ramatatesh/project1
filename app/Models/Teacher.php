<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    protected $fillable = ['gender','user_id','specialization','grade','teaching_years'];
    protected $table = 'teachers';
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
