<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GroupUserService;
use Illuminate\Http\Request;

class GroupUserController extends Controller
{
    protected $groupUserService;

    public function __construct(GroupUserService $groupUserService)
    {
        $this->groupUserService = $groupUserService;
    }

    public function addUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
             
        ]);

        $this->groupUserService->addUserToGroup($validated);

        return response()->json(['message' => 'User added to group']);
    }

    public function removeUser(Request $request, $userId, $groupId)
    {
        $this->groupUserService->removeUserFromGroup($userId, $groupId);

        return response()->json(['message' => 'User removed from group']);
    }

    public function listUsersInGroup($groupId)
    {
        $users = $this->groupUserService->listUsersInGroup($groupId);
        return response()->json(['users' => $users]);
    }

    public function listGroupsForUser($userId)
    {
        $groups = $this->groupUserService->listGroupsForUser($userId);
        return response()->json(['groups' => $groups]);
    }
}
