<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $credentials): ?array
    {
        $token = Auth::attempt($credentials);

        if (!$token) {
            return null;
        }

        $user = Auth::user();

        return [
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'token' => $token,
        ];
    }

    public function me(): array
    {
        $user = Auth::user();

        return [
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
