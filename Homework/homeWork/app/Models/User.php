<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     protected $table = 'users';
     protected $primaryKey = 'id'; 
     
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'firebase_token',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function getJWTIdentifier()
        {
    return $this->getKey();
          }

public function getJWTCustomClaims()
{
    return [];
}
    // علاقة المستخدم بالمجموعات التي يملكها
    public function ownedGroups()
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

     // علاقة المستخدم بالمجموعات التي هو عضو فيها
     public function groups()
     {
         return $this->belongsToMany(Group::class, 'group_user')->withPivot('status', 'role');
     }


     // علاقة المستخدم بالملفات المحجوزة
    public function reservedFiles()
    {
        return $this->hasMany(File::class, 'reserved_by');
    }

     // علاقة المستخدم بسجلات الملفات
     public function fileLogs()
     {
         return $this->hasMany(FileLog::class);
     }

    public function memberLogs()
    {
        return $this->hasMany(MemberLog::class);
    }
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
