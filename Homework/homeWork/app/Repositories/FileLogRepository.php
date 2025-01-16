<?php

namespace App\Repositories;

use App\Models\FileLog;
use App\Models\MemberLog;


class FileLogRepository
{
    public function createLog($fileId, $userId, $action,$groupId)
    {
        return FileLog::create([
            'file_id' => $fileId,
            'user_id' => $userId,
            'action' => $action,
            'group_id' => $groupId,
        ]);
    }

   
   public function getLogsByFile($fileId)
   {
       return FileLog::where('file_id', $fileId)->get();
   }

   public function getMemberLogsByGroup($groupId)
{
    return FileLog::where('group_id', $groupId)->get();
}
}
