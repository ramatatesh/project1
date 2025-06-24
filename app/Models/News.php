<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;
    protected $fillable=['title','content','grade_id'];
    protected $table = 'news';

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

}
