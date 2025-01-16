<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function findUserByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function getAllUsersWithIdAndName()
    {
        return User::select('id','name')->get();
    }
}
