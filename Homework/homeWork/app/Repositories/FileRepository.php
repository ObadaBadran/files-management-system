<?php

namespace App\Repositories;

use App\Models\File;
use App\Models\GroupUser;
use App\Models\Group;
use App\Models\FileBackup;



class FileRepository
{
    public function addFile($data)
    {
        return File::create($data);
    }

    public function getGroupIdByFile($fileId)
    {
        // اكتب المنطق هنا للحصول على groupId بناءً على fileId
        // مثال على استعلام:
        return \DB::table('files')
            ->where('id', $fileId)
            ->value('group_id');
    }

    public function addFileWithApproval($fileData)
    {
        return File::create([
            'group_id' => $fileData['group_id'],
            'user_id' => $fileData['user_id'],
            'path' => $fileData['path'],
            'name' => $fileData['name'],
            'is_approved' => $fileData['is_approved'] ?? false,
        ]);
    }

    public function getReservedFiles($groupId)
    {
        return File::where('group_id', $groupId)
            ->where('status', 'reserved')
            ->get();
    }

    public function findById($fileId)
    {
        return File::findOrFail($fileId);
    }

    public function getFileBackups($fileId)
    {
        return FileBackup::where('file_id', $fileId)->get();
    }

    public function findBackupById($fileId, $backupId)
    {
        // تأكد من أن النموذج Backup يحتوي على البيانات الصحيحة
        return FileBackup::where('file_id', $fileId)
                    ->where('id', $backupId)
                    ->first();
    }

    public function findFilesByIds(array $fileIds)
    {
        return File::whereIn('id', $fileIds)->get();
    }

    public function reserveMultipleFiles(array $fileIds, $userId)
    {
        return File::whereIn('id', $fileIds)
            ->where('status', 'free')
            ->update(['status' => 'reserved', 'reserved_by' => $userId]);
    }

    public function findAvailableFilesForUser($userId)
    {
        return File::where('status', 'free')
            ->whereHas('groupFile.members', function ($query) use ($userId) {
                $query->where('id', $userId);
            })->get();
    }

    public function delete($fileId)
    {
        return File::destroy($fileId);
    }
    public function all($groupId)
{
    return File::where('group_id', $groupId)
        ->get()
        ->map(function ($file) {
            // إذا كان المسار يحتوي على 'public/' في البداية، نزيلها
            if (strpos($file->path, 'public/') === 0) {
                $filePath = str_replace('public/', '', $file->path);
                // التأكد من أن الرابط يبدأ بـ 'storage/public/files/'
                $file->path = url('storage/public/files/' . $filePath);
            } else {
                // إذا كان المسار لا يحتوي على 'public/'، نضيفه بشكل صحيح
                $file->path = url('storage/public/' . $file->path);
            }
            return $file;
        });
}
  

    public function getFreeFilesByGroup($groupId)
    {
        return File::where('group_id', $groupId)
            ->where('status', 'free')
            ->get()
             ->map(function($file){
            //     if(strpos($file->path ,'public/') === 0){
            //         $filePath = str_replace('public/', '', $file->path);
            //         $file->path = url('storage/' . $filePath);
            //     }else{
            //         $file->path = url('storage/' . $file->path);
            //     }
                return $file;
            });
    }

    public function isUserInGroup($userId, $groupId)
    {
        return GroupUser::where('user_id', $userId)
            ->where('group_id', $groupId)
            ->exists();
    }

    public function getGroupOwnerIdByFileId($fileId)
    {
        $file = File::findOrFail($fileId);
        return $file->group->owner_id;
    }

    public function getPendingFilesForApproval($groupId)
    {
        return File::where('group_id', $groupId)
            ->where('is_approved', false)
            ->get();
    }

    public function approveFile($fileId, $status)
    {
        $file = File::find($fileId);
        if ($file) {
            $file->is_approved = $status;
            $file->save();
            return $file;
        }
        return null;
    }

    public function isMember($groupId, $userId)
    {
        return GroupUser::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function getGroupMembers($groupId)
    {
        return GroupUser::where('group_id', $groupId)
            ->join('users', 'group_users.user_id', '=', 'users.id')
            ->get(['users.id', 'users.name', 'users.email']);
    }

    public function isOwner($groupId, $userId)
    {
        return Group::where('id', $groupId)
            ->where('owner_id', $userId)
            ->exists();
    }
}
