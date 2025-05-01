<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $fillable =['job','gender','user_id'];
    protected $table = 'employees';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
