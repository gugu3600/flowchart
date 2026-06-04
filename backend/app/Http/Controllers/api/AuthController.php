<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
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

        return $this->success(
            ['user' => new UserResource($result['user'])],
            'User registered successfully',
            201,
        )->cookie('jwt_token', $result['token'], 43200, '/', null, true, true, false, 'Strict');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return $this->error(null, 'Invalid credentials', 401);
        }

        return $this->success(
            ['user' => new UserResource($result['user'])],
            'Login successful',
        )->cookie('jwt_token', $result['token'], 43200, '/', null, true, true, false, 'Strict');
    }

    public function me(): JsonResponse
    {
        return $this->success(
            new UserResource($this->authService->me()['user']),
            'Authenticated user retrieved',
        );
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->success([], 'Logged out successfully')
            ->cookie('jwt_token', '', -1, '/');
    }
}
