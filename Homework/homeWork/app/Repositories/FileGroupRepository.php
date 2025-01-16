<?php

namespace App\Repositories;

use App\Models\FileLog;


class FileGroupRepository
{
    public function create(array $data)
    {
        return GroupFile::create($data);
    }

    public function addMember($groupId, $userId)
    {
        $group = GroupFile::findOrFail($groupId);
        $group->members()->attach($userId);
    }

    public function findByOwner($ownerId)
    {
        return GroupFile::where('owner_id', $ownerId)->with('members')->get();
    }
}