<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    protected $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function create(Request $request)
    {
        $data = $request->only(['name']);
        $data['owner_id'] = auth()->id(); // تعيين صاحب المجموعة كالمستخدم الحالي
        $group = $this->groupService->createGroup($data);

        return response()->json($group, 201);
    }

    public function update(Request $request, $groupId)
    {
        $data = $request->only(['name']);
        $group = $this->groupService->updateGroup($groupId, $data);

        return response()->json($group);
    }

    public function delete($groupId)
    {
        $this->groupService->deleteGroup($groupId);
        return response()->json(['message' => 'Group deleted successfully.']);
    }

    public function showMembers($groupId)
    {
        $members = $this->groupService->getMembers($groupId);
        return response()->json($members);
    }

    public function invite(Request $request, $groupId)
    {
        $userIds = $request->input('user_ids'); // يجب إرسال قائمة بمعرفات المستخدمين
        $this->groupService->inviteMembers($groupId, $userIds);

        return response()->json(['message' => 'Invitations sent successfully.']);
    }
    

    public function respondToInvite(Request $request, $groupId)
    {
        $status = $request->input('status'); // يجب إرسال الحالة ('accepted' أو 'rejected')
        $response = $this->groupService->respondToInvite($groupId, $status);

        return response()->json(['message' => $response['message']], $response['status']);
    }


    public function getUserInvitations(Request $request)
    {
        $userId = $request->user()->id;
        $invitations = $this->groupService->getUserPendingInvitations($userId);

        return response()->json($invitations);
    }

    public function getAllUserGroups(): JsonResponse
    {
        $groups = $this->groupService->getAllGroupsForUser();

        return response()->json([
            'status' => 'success',
            'data' => $groups
        ]);
    }
}
