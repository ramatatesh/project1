<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;

    protected $fillable = ['username','father_name','mother_name','email','password',
    'role','phone','address','gender','birth_date','nationality','first_name','last_name','fcm_token',];

    protected $table = 'users';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
           // 'password' => 'hashed',
        ];
    }


    public function admain()
    {
    return $this->hasOne(Admain::class);
    }
    public function student()
    {
    return $this->hasOne(Student::class);
    }
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token, $this->email));
    }
}

