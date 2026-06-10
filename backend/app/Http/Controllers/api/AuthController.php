<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tier' => ['required', 'string', Rule::in(['silver', 'gold', 'platinum'])],
            'payment_method' => ['required', 'string', Rule::in(['kbzpay', 'ayapay', 'cbpay', 'mmqr'])],
        ]);

        $user = auth()->user();
        $tierRole = $validated['tier'];

        if ($user->hasRole('super-admin')) {
            return $this->error(null, 'Super-admin cannot change tier', 422);
        }

        $user->syncRoles([$tierRole]);

        $durations = [
            'silver' => 33,
            'gold' => 37,
            'platinum' => 44,
        ];

        if (isset($durations[$tierRole])) {
            $days = $durations[$tierRole];
            $now = now();
            $currentExpiry = $user->subscription_expires_at;
            $base = ($currentExpiry && $currentExpiry->isFuture()) ? $currentExpiry : $now;
            $user->subscription_expires_at = $base->copy()->addDays($days);
            $user->save();
        }

        $user->load('roles');

        return $this->success([
            'user' => new UserResource($user),
            'message' => "Subscribed to {$tierRole} tier successfully",
        ], 'Subscription successful');
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
