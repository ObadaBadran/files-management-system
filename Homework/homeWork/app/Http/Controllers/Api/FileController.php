<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileService;
use App\Services\FileLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Group;
use App\Models\File;


class FileController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService, FileLogService $fileLogService)
    {
        $this->fileService = $fileService;
        $this->fileLogService = $fileLogService;

    }
/********************************show files*********************************************** */

    public function show($group_id){
        $data = $this->fileService->allFile($group_id);
        if (!$data) {
            return response()->json(['message' => 'Failed to add file'], 400);
        }
        return response()->json($data);
      }

//**************************create files*************************************************** */
public function addFile(Request $request)
{
    $request->validate([
        'file' => 'required|file|max:10240', 
        'name' => 'required|string|max:255',
    ]);

    $fileData = [
        'name' => $request->input('name'),
        'file' => $request->file('file'), 
        'group_id' => $request->route('groupId'),
        'status' => 'free',
        'reserved_by' => null,
    ];

    $savedFile = $this->fileService->addFileToGroup($fileData);

    if ($savedFile) {
        return response()->json(['message' => 'File added successfully', 'file' => $savedFile], 201);
    }

    return response()->json(['message' => 'Failed to add file'], 400);
}

      /********************************************reserve files****************************************** */
    
    public function reserve(Request $request, $group_id)
    {
        $userId = auth()->id();
       
        $request->validate([
            'file_ids' => 'required|array|min:1', 
            'file_ids.*' => 'integer' 
        ]);

        $fileIds = $request->input('file_ids');

        if (!$this->fileService->isMember($group_id, $userId)) {
            return response()->json(['message' => 'You are not a member of this group.'], 403);
        }

        // التحقق من الملفات غير الموجودة
        $existingFiles = File::whereIn('id', $fileIds)->pluck('id')->toArray();
        $missingFiles = array_diff($fileIds, $existingFiles);

        if (!empty($missingFiles)) {
            return response()->json([
                'message' => 'Some files do not exist.',
                'missing_files' => $missingFiles
            ], 400);
        }

        // حجز الملفات
        $reservedFiles = $this->fileService->reserveFiles($fileIds, $userId);

        if (!empty($reservedFiles['download_links'])) {
            // تسجيل الإجراءات
            foreach ($fileIds as $fileId) {
                \Log::info('Logging action', ['fileId' => $fileId, 'userId' => $userId]);
                // تسجيل الإجراء في سجل النشاط
                $this->fileLogService->logAction($fileId, $userId, 'in-check', $group_id);
            }
            return response()->json($reservedFiles);
        }

        return response()->json(['message' => 'Failed to reserve files.'], 500);
    }

    /************************************release files********************************************************* */
    
    public function release($group_id, $fileId)
    {
        $userId = auth()->id();
    
        // التحقق من أن المستخدم عضو في المجموعة
        if (!$this->fileService->isMember($group_id, $userId)) {
            return response()->json(['message' => 'You are not a member of this group.'], 403);
        }
    
        // تسجيل ديباج لمعرفة البيانات المرسلة
        \Log::info('Request data:', request()->all());
    
        // التحقق من وجود ملف جديد في الطلب
        if (!request()->hasFile('new_file')) {
            \Log::error('No file received.');
            return response()->json(['message' => 'No new file provided.'], 400);
        }
    
        $file = request()->file('new_file');
        
        // تسجيل بيانات الملف
        \Log::info('File details:', [
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize()
        ]);
    
        // حفظ الملف الجديد في السيرفر والحصول على المسار
        $newFilePath = $file->store('files');
    
        // تحرير الملف واستبدال الملف القديم بالجديد
        $released = $this->fileService->releaseFile($fileId, $userId, $newFilePath);
    
        if ($released) {
            $this->fileLogService->logAction($fileId, $userId, 'out-check', $group_id);
            return response()->json(['message' => 'File successfully released.']);
        }
    
        return response()->json(['message' => 'Failed to release file.'], 500);
    }
    
/**************************************************free files************************************************** */

public function listFreeFilesInGroup($groupId)
{
    $userId = auth()->id(); // الحصول على معرف المستخدم الحالي
    $freeFiles = $this->fileService->listFreeFilesInGroup($userId, $groupId);

    if ($freeFiles === null) {
        return response()->json(['message' => 'User is not a member of this group.'], 403);
    }

    return response()->json([
        'message' => 'Free files fetched successfully',
        'files' => $freeFiles
    ]);
}

/*****************************************pending files to approve****************************************************** */
 // دالة لعرض الملفات المعلقة للموافقة من قبل صاحب المجموعة
 public function showPendingFiles($groupId)
 {
     // التأكد من أن المستخدم هو صاحب المجموعة
     if (!$this->isGroupOwner($groupId)) {
         return response()->json(['error' => 'You are not the owner of this group'], 403);
     }

     $pendingFiles = $this->fileService->getPendingFilesForApproval($groupId);
     return response()->json(['pending_files' => $pendingFiles], 200);
 }
/////*******************************************approve or reject***************************************************** */
 public function approveOrRejectFile(Request $request, $group_id, $fileId)
{
    // التأكد من أن المستخدم هو صاحب المجموعة
    if (!$this->isGroupOwner($group_id)) {
        return response()->json(['error' => 'You are not the owner of this group'], 403);
    }

    // الحصول على حالة الموافقة (يجب أن تكون 1 للموافقة أو 0 للرفض)
    $status = $request->input('status'); 

    if (!in_array($status, [0, 1])) {
        return response()->json(['error' => 'Invalid status value'], 400);
    }

    $updatedFile = $this->fileService->approveOrRejectFile($fileId, $status);

    if ($updatedFile) {
        return response()->json(['message' => 'File approval status updated successfully', 'file' => $updatedFile], 200);
    }

    return response()->json(['error' => 'File not found'], 404);
}
//******************************************************************************************** */
 // دالة للتحقق إذا كان المستخدم هو صاحب المجموعة
 private function isGroupOwner($groupId)
 {
     $userId = Auth::id();
     $group = Group::find($groupId);
     return $group && $group->owner_id == $userId;
 }

//**************************************************************************************************** */
public function checkUserRoleInGroup($groupId)
{
    $userId = Auth::id();

    if (!$groupId) {
        return response()->json(['error' => 'group_id مفقود'], 400);
    }

    $group = Group::find($groupId);
    if (!$group) {
        return response()->json(['error' => 'المجموعة غير موجودة'], 404);
    }

    // التحقق من دور المستخدم في المجموعة
    if ($group->owner_id == $userId) {
        return response()->json(['role' => 'owner'], 200);
    }

    // التحقق من العضوية باستخدام طريقة أكثر كفاءة لتقليل الذاكرة
    $isMember = $group->members()->where('user_id', $userId)->exists();

    if ($isMember) {
        return response()->json(['role' => 'member'], 200);
    }

    return response()->json(['role' => 'none'], 200);
}

public function restoreBackup($fileId, $backupId)
{
    $userId = Auth::id(); // الحصول على ID المستخدم من الجلسة

    // استدعاء الخدمة لاسترجاع النسخة الاحتياطية
    $file = $this->fileService->restoreBackup($fileId, $backupId, $userId);

    if ($file) {
        return response()->json(['message' => 'Backup restored successfully.', 'file' => $file]);
    }

    return response()->json(['message' => 'Failed to restore backup.'], 400);
}


public function getFileVersions($fileId)
{
    $result = $this->fileService->getFileVersions($fileId);

    if (!$result['success']) {
        return response()->json(['message' => $result['message']], 404);
    }

    return response()->json([
        'message' => $result['message'],
        'backups' => $result['data'],
    ]);
}


  
}
