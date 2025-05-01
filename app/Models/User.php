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

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'father_name',
    ];
    protected $table = 'users';
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
//    protected $hidden = [
//        'password',
//        'remember_token',
//    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function students(): HasMany
    {
return $this->hasMany(Student::class);
    }
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }
    public function admains(): HasMany
    {
        return $this->hasMany(Admain::class);
    }
    public function employees(): HasMany
    {
        return $this->hasMany(Admain::class);
    }


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token, $this->email));
    }
}

