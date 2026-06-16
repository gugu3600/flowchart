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

        // assign selected tier role (defaults to free)
        $tier = $data['tier'] ?? 'free';
        $user->assignRole($tier);

        $token = auth()->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return ['user' => $user, 'token' => $token];
    }
}
