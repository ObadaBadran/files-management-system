<?php

namespace App\Services;

use App\Repositories\GroupRepository;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InviteResponseNotification;
use App\Notifications\GroupInviteNotification;
use App\Models\User;
use App\Models\Group;



class GroupService
{
    protected $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function createGroup(array $data)
    {
        return $this->groupRepository->create($data);
    }

    public function updateGroup($groupId, array $data)
    {
        $userId = Auth::id();
        if ($this->isOwner($groupId, $userId)){
            return $this->groupRepository->update($groupId, $data);
        }
       return false;
    }

    public function deleteGroup($groupId)
    {
        $userId = Auth::id();
        if ($this->isOwner($groupId, $userId)) {
            return $this->groupRepository->delete($groupId);
        }
       return false;
    }

    public function getMembers($groupId)
    {
        return $this->groupRepository->findMembers($groupId);
    }

    public function inviteMembers($groupId, array $userIds)
    {
        $group = $this->groupRepository->findById($groupId);
        foreach ($userIds as $userId) {
            $group->members()->attach($userId, ['status' => 'pending']);
            
            // إرسال إشعار للمستخدم المدعو
            $user = User::find($userId);
            $user->notify(new GroupInviteNotification($group->name));
        }
    }

    public function respondToInvite($groupId, $status)
{
    $userId = Auth::id();
    $invite = $this->groupRepository->findInvite($groupId, $userId);

    if (!$invite) {
        return ['error' => 'Invitation not found', 'status' => 404];
    }

    $this->groupRepository->updateInviteStatus($groupId, $userId, $status);

    // إخطار صاحب المجموعة بتحديث حالة الدعوة
    $group = $this->groupRepository->findById($groupId); // استرجاع تفاصيل المجموعة
    $groupOwner = User::find($group->owner_id); // الحصول على صاحب المجموعة باستخدام owner_id

    // تحقق إذا كان هناك صاحب للمجموعة قبل الإخطار
    if ($groupOwner) {
        $groupOwner->notify(new InviteResponseNotification($status, Auth::user()->name));
    }

    return ['message' => "Invitation $status successfully", 'status' => 200];
}


    public function getUserPendingInvitations($userId)
    {
        return $this->groupRepository->getPendingInvitationsByUser($userId);
    }

    public function getAllGroupsForUser()
    {
        $userId = Auth::id();

        // جلب المجموعات التي أنشأها المستخدم
        $ownedGroups = $this->groupRepository->getOwnedGroups($userId);

        // جلب المجموعات التي يكون عضوًا فيها
        $memberGroups = $this->groupRepository->getMemberGroups($userId);

        // دمج المجموعات في قائمة واحدة
        $allGroups = $ownedGroups->merge($memberGroups)->unique('id')->values();

        return $allGroups;
    }

    private function isOwner($groupId, $userId)
    {
        // تحقق إذا كان المستخدم هو صاحب المجموعة
        return Group::where('id', $groupId)->where('owner_id', $userId)->exists();
    }
    

}
