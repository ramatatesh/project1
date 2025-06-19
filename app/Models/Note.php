<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['student_id','content'];
    protected $table = 'notes';
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function admain()
    {
        return $this->belongsTo(Admain::class);
    }
}
