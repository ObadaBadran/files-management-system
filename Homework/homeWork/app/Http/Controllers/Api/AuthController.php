<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:member,admin',
        ]);

        $user = $this->userService->registerUser($data);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $loginData = $this->userService->loginUser($data);

        return response()->json($loginData, 200);
    }

    public function logout()
    {
        $this->userService->logoutUser();

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

    public function getUsersWithIdAndName()
    {
        $users = $this->userService->getAllUsersWithIdAndName();
        return response()->json($users);
    }

    public function saveFcmToken(Request $request)
{
    // التحقق من وجود التوكن في body
    $fcmToken = $request->input('fcm_token');
    $user = auth()->user(); // الحصول على المستخدم المصادق عليه

    if (!$fcmToken) {
        return response()->json([
            'message' => 'FCM Token is required'
        ], 400);
    }

    // قم بحفظ التوكن في قاعدة البيانات أو أي عملية أخرى
    $user->fcm_token = $fcmToken;
    $user->save();

    return response()->json([
        'message' => 'FCM Token saved successfully'
    ], 200);
}
   

    
}
