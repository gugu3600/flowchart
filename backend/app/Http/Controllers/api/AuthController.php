<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::guard('api')->attempt($request->only('email', 'password'));

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'User registered successfully', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $token = Auth::guard('api')->attempt($request->only('email', 'password'));

        if (!$token) {
            return $this->error(null, 'Invalid credentials', 401);
        }

        $user = Auth::guard('api')->user();
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        return $this->success([
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
            'token' => $token,
        ], 'Login successful');
    }

    public function me(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        return $this->success([
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 'Authenticated user retrieved');
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return $this->success([], 'Logged out successfully');
    }
}
