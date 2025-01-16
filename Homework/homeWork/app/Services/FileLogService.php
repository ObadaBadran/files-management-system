<?php

namespace App\Services;

use App\Repositories\FileLogRepository;
use App\Models\Group;

class FileLogService
{
    protected $fileLogRepo;

    public function __construct(FileLogRepository $fileLogRepo)
    {
        $this->fileLogRepo = $fileLogRepo;
    }

    public function logAction($fileId, $userId, $action,$groupId)
    {
        return $this->fileLogRepo->createLog($fileId, $userId, $action,$groupId);
    }

    // جلب السجلات على مستوى ملف معين
    public function getLogsForFile($fileId)
    {
        return $this->fileLogRepo->getLogsByFile($fileId);
    }

    // جلب السجلات على مستوى الأعضاء ضمن مجموعة معينة بعد التأكد من أن المستخدم هو صاحب المجموعة
    public function getMemberLogsForGroup($groupId, $userId)
    {
        // تحقق إذا كان المستخدم صاحب المجموعة
        if (!$this->isGroupOwner($groupId, $userId)) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // إرجاع السجلات بعد التأكد من أن المستخدم هو صاحب المجموعة
        return $this->fileLogRepo->getMemberLogsByGroup($groupId);
    }

    // التحقق إذا كان المستخدم هو صاحب المجموعة
    private function isGroupOwner($groupId, $userId)
    {
        $group = Group::find($groupId);
        return $group && $group->owner_id === $userId;
    }
}
