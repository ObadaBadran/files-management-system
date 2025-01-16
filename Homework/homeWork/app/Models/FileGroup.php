<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'owner_id'
    ];

   // علاقة مجموعة الملفات بالملفات الخاصة بها
   public function files()
   {
       return $this->hasMany(File::class, 'file_group_id');
   }

   // علاقة المجموعة بالمالك (المستخدم الذي أنشأها)
   public function owner()
   {
       return $this->belongsTo(User::class, 'owner_id');
   }
}
