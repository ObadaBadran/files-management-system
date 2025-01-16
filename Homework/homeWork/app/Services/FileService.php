<?php

namespace App\Services;

use App\Repositories\FileRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\GroupUser;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;
class FileService
{
    protected $fileRepository;
    protected $firebaseMessaging;
    

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->firebaseMessaging = (new Factory)->withServiceAccount(config('firebase.credentials.file'))->createMessaging();
    }

    public function notifyGroupMembers($groupId, $title, $body)
    {
        $members = GroupUser::where('group_id', $groupId)->get();
    
        foreach ($members as $member) {
            $token = $member->user->firebase_token;
    
            if ($token) {
                $message = CloudMessage::new()
                    ->withNotification(Notification::create($title, $body))
                    ->withData(['group_id' => $groupId]);
    
                $this->firebaseMessaging->sendToDevice($token, $message);
            }
        }
    }
    public function addFileToGroup($fileData)
    {
    $userId = Auth::id();
    if (!$userId) {
        throw new \Exception("المستخدم غير مسجل دخول.");
    }

    if (empty($fileData['group_id'])) {
        throw new \Exception("البيانات غير مكتملة: group_id مفقود.");
    }

    $fileData['user_id'] = $userId;

    // التحقق وتخزين الملف
    if (isset($fileData['file']) && !isset($fileData['path'])) {
        $fileData['path'] = $this->storeFile($fileData['file']);
    }

    if (!$fileData['path']) {
        throw new \Exception("لم يتم تخزين الملف.");
    }

    $groupId = $fileData['group_id'];

    // التحقق من الصلاحيات
    if ($this->fileRepository->isOwner($groupId, $userId)) {
        $fileData['is_approved'] = true;
        return $this->fileRepository->addFile($fileData);
    } elseif ($this->fileRepository->isMember($groupId, $userId)) {
        $fileData['is_approved'] = false;
        return $this->fileRepository->addFileWithApproval($fileData);
    }

    throw new \Exception("المستخدم ليس لديه الصلاحيات المناسبة لإضافة الملف إلى المجموعة.");
}

// private function storeFile($file)
// {
//     $path = $file->store('public/files'); 
//     \Log::info("File stored at: " . $path);
//     return $path;
// }

private function storeFile($file)
{
    $path = $file->store('files', 'public'); // التخزين في storage/app/public/files
    \Log::info("File stored at: " . $path);
    return asset('storage/' . $path); // إنشاء رابط يمكن الوصول إليه عبر المتصفح
}


public function reserveFiles(array $fileIds, $userId)
{
    // حجز الملفات
    $reservedCount = $this->fileRepository->reserveMultipleFiles($fileIds, $userId);
    
    // طباعة العدد المحجوز
    \Log::info('Reserved files count: ' . $reservedCount);
    
    if ($reservedCount !== count($fileIds)) {
        throw new \Exception('Failed to reserve all files.');
    }

    // جلب الملفات المحجوزة
    $reservedFiles = $this->fileRepository->findFilesByIds($fileIds)->filter(function ($file) {
        return $file->status == 'reserved';
    });

    // طباعة الملفات المحجوزة
    \Log::info('Reserved files: ' . $reservedFiles->count());
    
    if ($reservedFiles->isEmpty()) {
        throw new \Exception('No files were successfully reserved.');
    }


     if (!empty($fileIds[0])) {
         $reservingUser = User::find($userId);
         $reservingUserName = $reservingUser ? $reservingUser->name : 'Unknown User';

         $this->notifyGroupMembers(
             $this->fileRepository->getGroupIdByFile($fileIds[0]),
             'File Reserved',
             "A file has been reserved by {$reservingUserName}."
         );
     }


    // إنشاء روابط تحميل
    foreach ($reservedFiles as $file) {
        // إزالة 'public/' من المسار عند إنشاء الرابط
        $filePath = str_replace(['public/', 'storage/'], '', $file->path); 
    
        // إضافة 'public/' بعد 'storage/'
        $downloadLinks[] = [
            'file_id' => $file->id,
            'download_link' => asset('storage/' . ltrim($filePath, '/')), // استخدام asset للحصول على الرابط الصحيح
        ];
    }
   

    return [
        'message' => 'Files reserved and ready for download.',
        'download_links' => $downloadLinks,
    ];
}


public function releaseFile($fileId, $userId, $newFilePath)
{
    $file = $this->fileRepository->findById($fileId);

    if ($file->status === 'reserved' && $file->reserved_by === $userId) {
        $this->createBackup($file->path, $file);

        if ($newFilePath && pathinfo($newFilePath, PATHINFO_EXTENSION) === pathinfo($file->path, PATHINFO_EXTENSION)) {
            $file->path = asset('storage/' .  ltrim($newFilePath, '/'));
        } else {
            throw new \Exception("الملف الجديد يجب أن يكون بنفس الاسم واللاحقة للملف القديم.");
        }

        $file->status = 'free';
        $file->reserved_by = null;
        $file->save();

         // إرسال إشعار إلى أعضاء المجموعة
       /*  $this->notifyGroupMembers(
            $this->fileRepository->getGroupIdByFile($fileId),
            'File Released',
            'A file has been released back to the group.'
        );*/


        return $file;
    }

    throw new \Exception('Unauthorized file release or file not reserved by user.');
}
    

public function listFreeFilesInGroup($userId, $groupId)
{
        if (!$this->fileRepository->isUserInGroup($userId, $groupId)) {
            return null;
        }
    
        return $this->fileRepository->getFreeFilesByGroup($groupId);   
}


   // دالة لجلب الملفات المعلقة للموافقة من قبل صاحب المجموعة
   public function getPendingFilesForApproval($groupId)
   { 
        return $this->fileRepository->getPendingFilesForApproval($groupId);  
   }

   // دالة للموافقة أو الرفض على الملفات
   public function approveOrRejectFile($fileId, $status)
   {
        return $this->fileRepository->approveFile($fileId, $status);
      
   }

   public function allFile($groupId){
           return $this->fileRepository->all($groupId);
   }

   private function createBackup($filePath, $file)
{
    // تعديل المسار للتأكد من التوافق مع التخزين
    $filePath = str_replace('/storage', '', $filePath); // إزالة 'storage/' إذا كانت مضافة
    $filePath = 'files/' . basename($filePath); // تأكد من أن المسار نسبي داخل 'storage/app'

    // طباعة المسار الذي يتم التحقق منه
    \Log::info("Checking file existence at: " . $filePath);

    // التأكد من وجود الملف
    if (!Storage::exists($filePath)) {
        \Log::error("Original file not found: " . $filePath);
        throw new \Exception("Original file not found: " . $filePath);
    }

    // نسخ الملف إلى مجلد النسخ الاحتياطية
    $backupPath = 'backups/' . basename($filePath);

    if (!Storage::copy($filePath, $backupPath)) {
        \Log::error("Failed to create backup: " . $filePath);
        throw new \Exception("Failed to create backup: " . $filePath);
    }

    \Log::info("Backup created successfully: " . $backupPath);

    // تخزين النسخة الاحتياطية في قاعدة البيانات
    \App\Models\FileBackup::create([
        'file_id' => $file->id,
        'backup_path' => $backupPath,
    ]);
}

public function restoreBackup($fileId, $backupId, $userId)
{
    \Log::info("Restoring backup: File ID $fileId, Backup ID $backupId, User ID $userId");

    $file = $this->fileRepository->findById($fileId);

    if (!$file) {
        \Log::error("File not found: ID $fileId");
        return response()->json(['message' => 'File not found.'], 404);
    }

    if ($file->reserved_by && $file->reserved_by !== $userId) {
        \Log::error("Unauthorized access: User $userId trying to access file reserved by {$file->reserved_by}");
        return response()->json(['message' => 'Unauthorized access.'], 403);
    }

    $backup = $this->fileRepository->findBackupById($fileId, $backupId);

    if (!$backup) {
        \Log::error("Backup not found for File ID $fileId with Backup ID $backupId");
        return response()->json(['message' => 'Backup not found.'], 404);
    }

    // Ensure the correct backup path
    $backupPath = storage_path('app/public/backups/' . basename($backup->backup_path));

    if (!file_exists($backupPath)) {
        \Log::error("Backup file not found on disk: $backupPath");
        return response()->json(['message' => 'Backup file not found on disk.'], 400);
    }

    // Update the file path to the restored backup path
    $file->path = '/storage/backups/' . basename($backup->backup_path);

    if (!$file->save()) {
        \Log::error("Failed to save file path for File ID $fileId");
        return response()->json(['message' => 'Failed to restore backup.'], 400);
    }

    \Log::info("Backup restored successfully for File ID $fileId");
    return response()->json(['message' => 'Backup restored successfully.', 'file' => $file]);
}

public function getFileVersions($fileId)
{
    \Log::info("Fetching all versions for File ID $fileId");

    // التأكد من وجود الملف
    $file = $this->fileRepository->findById($fileId);

    if (!$file) {
        \Log::error("File not found: ID $fileId");
        return ['success' => false, 'message' => 'File not found.', 'data' => null];
    }

    // جلب النسخ الاحتياطية
    $backups = $this->fileRepository->getFileBackups($fileId);

    if ($backups->isEmpty()) {
        \Log::info("No backups found for File ID $fileId");
        return ['success' => false, 'message' => 'No backups found.', 'data' => null];
    }

    // تعديل المسار الكامل
    $backups->transform(function ($backup) {
        $backup->backup_path = url('/storage/backups/' . basename($backup->backup_path));
        return $backup;
    });

    \Log::info("Found " . $backups->count() . " backups for File ID $fileId");

    return ['success' => true, 'message' => 'Backups retrieved successfully.', 'data' => $backups];
}


   public function isMember($groupId, $userId)
    {
        return GroupUser::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->exists();
    }
}
