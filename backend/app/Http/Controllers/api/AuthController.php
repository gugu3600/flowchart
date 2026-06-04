<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseController
{
    public function __construct(
        private readonly RegisterService $registerService,
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->registerService->register($request->validated());

        return $this->success($result, 'User registered successfully', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return $this->error(null, 'Invalid credentials', 401);
        }

        return $this->success($result, 'Login successful');
    }

    public function me(): JsonResponse
    {
        return $this->success(
            $this->authService->me(),
            'Authenticated user retrieved',
        );
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->success([], 'Logged out successfully');
    }
}
