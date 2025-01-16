<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileLogService;
use Illuminate\Support\Facades\Auth;

class FileLogController extends Controller
{
    protected $fileLogService;

    public function __construct(FileLogService $fileLogService)
    {
        $this->fileLogService = $fileLogService;
    }

    // جلب السجلات على مستوى ملف معين
    public function getFileLogs($fileId)
    {
        $logs = $this->fileLogService->getLogsForFile($fileId);
        return response()->json(['logs' => $logs]);
    }

    // جلب السجلات التي قام بها كل عضو (تحتاج إلى أن يكون المستخدم منشئ المجموعة)
    public function getMemberLogs($groupId)
    {
        $userId = Auth::id();

       

        $logs = $this->fileLogService->getMemberLogsForGroup($groupId,$userId);
        return response()->json(['logs' => $logs]);
    }

    
}
