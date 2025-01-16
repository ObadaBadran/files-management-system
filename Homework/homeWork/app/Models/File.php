<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'group_id',
        'status',
        'reserved_by',
        'path',
        'is_approved',
    ];

   

    // علاقة الملف بمجموعة الملفات
    public function groupFile()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    // علاقة الملف بالمستخدم الذي قام بحجزه
    public function reservedByUser()
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }

    // علاقة الملف بسجل العمليات التي تمت عليه
    public function logs()
    {
        return $this->hasMany(FileLog::class);
    }
}
