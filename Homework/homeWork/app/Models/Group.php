<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'owner_id',
    ];

   // علاقة المجموعة بمالكها
   public function owner()
   {
       return $this->belongsTo(User::class, 'owner_id');
   }

   public function files(){
    return $this->hasMany(File::class);
   }

   // علاقة المجموعة بالأعضاء
   public function members()
   {
       return $this->belongsToMany(User::class, 'group_user')->withPivot('status', 'role');
   }

    public function memberLogs()
    {
        return $this->hasMany(MemberLog::class);
    }
}
