<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AuthController extends BaseController
{
    public function __construct(
        private readonly RegisterService $registerService,
        private readonly AuthService $authService,
    ) {}

    private function jwtCookie(string $value, int $minutes): \Illuminate\Cookie\CookieJar|\Symfony\Component\HttpFoundation\Cookie
    {
        $cfg = config('jwt.cookie');
        return cookie(
            $cfg['name'],
            $value,
            $minutes,
            $cfg['path'],
            $cfg['domain'],
            (bool) $cfg['secure'],
            (bool) $cfg['http_only'],
            (bool) $cfg['raw'],
            $cfg['same_site']
        );
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->registerService->register($request->validated());

        return $this->success(
            ['user' => new UserResource($result['user'])],
            'User registered successfully',
            201,
        )->withCookie($this->jwtCookie($result['token'], config('jwt.ttl', 60)));
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if ($request->cookie(config('jwt.cookie.name'))) {
            try {
                if (auth('api')->setToken($request->cookie(config('jwt.cookie.name')))->authenticate()) {
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

        return $this->success(
            ['user' => new UserResource($result['user'])],
            'Login successful',
        )->withCookie($this->jwtCookie($result['token'], config('jwt.ttl', 60)));
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
            ->withCookie($this->jwtCookie('', -1));
    }

    public function refresh(): JsonResponse
    {
        try {
            $newToken = auth()->refresh();
        } catch (\Exception $e) {
            return $this->error(null, 'Token refresh failed. Please login again.', 401);
        }

        return $this->success([], 'Token refreshed')
            ->withCookie($this->jwtCookie($newToken, config('jwt.ttl', 60)));
    }

    private const TIER_RANK = [
        'free' => 0,
        'silver' => 1,
        'gold' => 2,
        'platinum' => 3,
    ];

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tier' => ['required', 'string', Rule::in(['silver', 'gold', 'platinum'])],
            'payment_method' => ['required', 'string', Rule::in(['kbzpay', 'ayapay', 'cbpay', 'mmqr'])],
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $tierRole = $validated['tier'];

        if ($user->hasRole('super-admin')) {
            return $this->error(null, 'Super-admin cannot change tier', 422);
        }

        $currentRole = collect($user->getRoleNames())->first(fn ($r) => isset(self::TIER_RANK[$r])) ?? 'free';

        if (self::TIER_RANK[$tierRole] <= self::TIER_RANK[$currentRole]) {
            return $this->error(
                null,
                "You are already on the {$currentRole} tier. Please choose a higher tier to upgrade.",
                422
            );
        }

        $durations = [
            'silver' => 33,
            'gold' => 37,
            'platinum' => 44,
        ];

        DB::transaction(function () use ($user, $tierRole, $durations) {
            $lockedUser = \App\Models\User::where('id', $user->id)->lockForUpdate()->first();

            $lockedUser->syncRoles([$tierRole]);

            if (isset($durations[$tierRole])) {
                $days = $durations[$tierRole];
                $now = now();
                $currentExpiry = $lockedUser->subscription_expires_at;
                $base = ($currentExpiry && $currentExpiry->isFuture()) ? $currentExpiry : $now;
                $lockedUser->subscription_expires_at = $base->copy()->addDays($days);
                $lockedUser->save();
            }
        });

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
