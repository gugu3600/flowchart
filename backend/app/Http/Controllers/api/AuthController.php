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

        $secure = config('app.env') === 'production';
        return $this->success(
            ['user' => new UserResource($result['user'])],
            'User registered successfully',
            201,
        )->cookie('jwt_token', $result['token'], 43200, '/', null, $secure, true, false, 'Strict');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if ($request->cookie('jwt_token')) {
            try {
                if (auth('api')->setToken($request->cookie('jwt_token'))->authenticate()) {
                    return $this->error(null, 'Already authenticated. Please logout first.', 409);
                }
            } catch (\Exception $e) {
                // Token is invalid/expired — allow login
            }
        }

        $result = $this->authService->login($request->validated());

        if (!$result) {
            return $this->error(null, 'Invalid credentials', 401);
        }

        $secure = config('app.env') === 'production';
        return $this->success(
            ['user' => new UserResource($result['user'])],
            'Login successful',
        )->cookie('jwt_token', $result['token'], 43200, '/', null, $secure, true, false, 'Strict');
    }

    public function me(): JsonResponse
    {
        return $this->success(
            ['user' => new UserResource($this->authService->me()['user'])],
            'Authenticated user retrieved',
        );
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->success([], 'Logged out successfully')
            ->cookie('jwt_token', '', -1, '/');
    }

    public function paymentMethods(): JsonResponse
    {
        $methods = [
            ['id' => 'kbzpay', 'label' => 'KBZ Pay', 'icon' => '💳'],
            ['id' => 'ayapay', 'label' => 'AYA Pay', 'icon' => '💳'],
            ['id' => 'cbpay', 'label' => 'CB Pay', 'icon' => '💳'],
            ['id' => 'mmqr', 'label' => 'MMQR', 'icon' => '📱'],
        ];

        return $this->success(['methods' => $methods], 'Payment methods retrieved');
    }
}
