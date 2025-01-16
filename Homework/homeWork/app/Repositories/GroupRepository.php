<?php

namespace App\Repositories;

use App\Models\Group;
use DB;

class GroupRepository
{
    public function create(array $data)
    {
        return Group::create($data);
    }

    public function findById($groupId)
    {
        return Group::findOrFail($groupId);
    }

    public function update($groupId, array $data)
    {
        $group = $this->findById($groupId);
        $group->update($data);
        return $group;
    }

    public function delete($groupId)
    {
        return Group::destroy($groupId);
    }

    public function all()
    {
        return Group::all();
    }

    public function findMembers($groupId)
    {
        $group = $this->findById($groupId);
        return $group->members;
    }

    public function findInvite($groupId, $userId)
    {
        return DB::table('group_user')
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->first();
    }

    public function updateInviteStatus($groupId, $userId, $status)
    {
       
        return DB::table('group_user')
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->update(['status' => $status]);
            //dd('u');
    }


    public function getPendingInvitationsByUser($userId)
{
    return Group::whereHas('members', function ($query) use ($userId) {
        $query->where('user_id', $userId)
              ->where('status', 'pending');
    })->get();
}

    // جلب المجموعات التي أنشأها المستخدم
    public function getOwnedGroups($userId)
    {
        return DB::table('groups')
            ->where('owner_id', $userId)
            ->select('id', 'name')
            ->get();
    }

    public function getMemberGroups($userId)
    {
        return DB::table('group_user')
            ->join('groups', 'group_user.group_id', '=', 'groups.id')
            ->where('group_user.user_id', $userId)
            ->where('group_user.status', 'accepted') // فقط المجموعات التي تم قبول العضوية فيها
            ->select('groups.id', 'groups.name')
            ->get();
    }
}
