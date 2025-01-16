<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\GroupUserController;
use App\Http\Controllers\Api\FileLogController;
use App\Http\Controllers\Api\MemberLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\TransactionMiddleware;
use App\Http\Middleware\ExceptionMiddleware;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('groups/{group_id}/check-role',[FileController::class,'checkUserRoleInGroup']);

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::get('/getUsers',[AuthController::class,'getUsersWithIdAndName']);
Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);


Route::middleware('auth:api')->group(function () {
    Route::post('/groups', [GroupController::class, 'create']);
    Route::put('/groups/{groupId}', [GroupController::class, 'update']);
    Route::delete('/groups/{groupId}', [GroupController::class, 'delete']);
    Route::get('/groups/{groupId}/members', [GroupController::class, 'showMembers']);
    Route::post('/groups/{groupId}/invite', [GroupController::class, 'invite']);
    Route::post('/groups/{groupId}/respond', [GroupController::class, 'respondToInvite']);
    Route::get('/groups/invitations', [GroupController::class, 'getUserInvitations']);
    Route::get('/user/groups', [GroupController::class, 'getAllUserGroups']);

});

// نقاط نهاية لإدارة الملفات
Route::middleware('auth:api')->prefix('files')->group(function () {
    Route::post('/groups/{groupId}/files', [FileController::class, 'addFile']);  
    Route::get('/{id}', [FileController::class, 'show']);
   // Route::put('/{group_id}/check-in', [FileController::class, 'reserve']);
   Route::middleware(['transaction'])->put('/{group_id}/check-in', [FileController::class, 'reserve']);
    Route::post('/{group_id}/{fileId}/check-out', [FileController::class, 'release'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::delete('/{id}', [FileController::class, 'destroy']);
    Route::get('/groups/{groupId}/free', [FileController::class, 'listFreeFilesInGroup']);
   
    Route::get('/groups/{groupId}/pending-files', [FileController::class, 'showPendingFiles']);

    // الموافقة أو الرفض على الملفات
   Route::post('/{group_id}/files/{fileId}/approve-or-reject', [FileController::class, 'approveOrRejectFile']);
   Route::get('/files/{group_id}',[FileController::class,'show'])->name('show');
   Route::post('/file/restore/{fileId}/{backupId}', [FileController::class, 'restoreBackup']);
   Route::get('/files/{fileId}/versions', [FileController::class, 'getFileVersions']);
   
});

Route::post('/save-fcm-token', [AuthController::class, 'saveFcmToken'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
// نقاط نهاية لسجلات الملفات
Route::prefix('file-logs')->group(function () {
    Route::get('/files/{fileId}/logs', [FileLogController::class, 'getFileLogs']);
    Route::get('/groups/{groupId}/member-logs', [FileLogController::class, 'getMemberLogs']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/member-logs', [MemberLogController::class, 'create']);
    Route::get('/member-logs', [MemberLogController::class, 'index']);
    Route::get('/member-logs/{id}', [MemberLogController::class, 'show']);
});

//notifications
Route::middleware('auth:api')->get('/notifications', [NotificationController::class, 'getUserNotifications']);
Route::middleware('auth:api')->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);


Route::get('/test-notifications', function () {
    $fileService = app(\App\Services\FileService::class);

    // معرف المجموعة (Group ID) لاختبار الإشعارات
    $groupId = 1; // استبدل بـ Group ID صالح
    $title = 'Test Notification';
    $body = 'This is a test notification sent to group members.';

    try {
        $fileService->notifyGroupMembers($groupId, $title, $body);
        return 'Notification sent successfully.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});


