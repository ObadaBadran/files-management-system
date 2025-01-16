<?php

namespace App\Repositories;

use App\Models\MemberLog;

class MemberLogRepository
{
    public function createMemberLog(array $data)
    {
        return MemberLog::create($data);
    }

    public function findMemberLogById($id)
    {
        return MemberLog::find($id);
    }

    public function getAllMemberLogs()
    {
        return MemberLog::all();
    }
}
