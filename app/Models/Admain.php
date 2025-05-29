<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admain extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','specialization','grade'];
    protected $table = 'admains';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

}
