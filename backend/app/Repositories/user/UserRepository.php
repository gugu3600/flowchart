<?php

namespace App\Repositories\user;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function find(int $id)
    {
        return User::find($id);
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data)
    {
        return User::create($data);
    }
}
