<?php

namespace App\Repositories;

use App\Models\GroupUser;

class GroupUserRepository
{
    public function addUserToGroup(array $data)
    {
        return GroupUser::create($data);
    }

    public function removeUserFromGroup($userId, $groupId)
    {
        return GroupUser::where('user_id', $userId)->where('group_id', $groupId)->delete();
    }

    public function getUsersByGroup($groupId)
    {
        return GroupUser::where('group_id', $groupId)->with('user')->get();
    }

    public function getGroupsByUser($userId)
    {
        return GroupUser::where('user_id', $userId)->with('group')->get();
    }
}
