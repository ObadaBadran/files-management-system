<?php

namespace App\Services;

use App\Repositories\GroupUserRepository;

class GroupUserService
{
    protected $groupUserRepository;

    public function __construct(GroupUserRepository $groupUserRepository)
    {
        $this->groupUserRepository = $groupUserRepository;
    }

    public function addUserToGroup(array $data)
    {
        return $this->groupUserRepository->addUserToGroup($data);
    }

    public function removeUserFromGroup($userId, $groupId)
    {
        return $this->groupUserRepository->removeUserFromGroup($userId, $groupId);
    }

    public function listUsersInGroup($groupId)
    {
        return $this->groupUserRepository->getUsersByGroup($groupId);
    }

    public function listGroupsForUser($userId)
    {
        return $this->groupUserRepository->getGroupsByUser($userId);
    }
}
