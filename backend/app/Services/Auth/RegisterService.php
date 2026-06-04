<?php

namespace App\Services\Auth;

use App\Repositories\user\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = auth()->guard('api')->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return ['user' => $user, 'token' => $token];
    }
}
