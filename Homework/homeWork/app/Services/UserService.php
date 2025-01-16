<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerUser(array $data)
    {
        return $this->userRepository->createUser($data);
    }

    public function loginUser(array $data)
    {
        $user = $this->userRepository->findUserByEmail($data['email']);

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials provided.'],
            ]);
        }

        // إنشاء التوكن باستخدام JWT Auth
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logoutUser()
    {
        // حذف التوكن باستخدام JWT Auth
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function getAllUsersWithIdAndName()
    {
        return $this->userRepository->getAllUsersWithIdAndName();
    }
}
