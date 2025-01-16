<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'user_id',
        'action',
        'group_id',  
    ];

    // علاقة سجل الملفات بالملف
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    // علاقة سجل الملفات بالمستخدم الذي قام بالعملية
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة سجل الملفات بالمجموعة
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
