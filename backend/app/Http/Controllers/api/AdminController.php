<?php

namespace App\Http\Controllers\api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminController extends BaseController
{
    public function stats(): JsonResponse
    {
        $userCount = User::count();
        $flowCount = \App\Models\Flow::count();
        $tableCount = \App\Models\TableDefinition::count();
        $logicCount = \App\Models\LogicDefinition::count();

        return $this->success([
            'stats' => [
                ['label' => 'Users', 'value' => (string) $userCount, 'icon' => '&#128101;', 'color' => '#3b82f6'],
                ['label' => 'Flows', 'value' => (string) $flowCount, 'icon' => '&#128196;', 'color' => '#8b5cf6'],
                ['label' => 'Tables', 'value' => (string) $tableCount, 'icon' => '&#128202;', 'color' => '#10b981'],
                ['label' => 'Logics', 'value' => (string) $logicCount, 'icon' => '&#9881;', 'color' => '#f59e0b'],
            ],
        ], 'Stats retrieved');
    }

    public function users(): JsonResponse
    {
        $users = User::with('roles')->orderBy('created_at', 'desc')->paginate(20);

        return $this->success([
            'users' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 'Users retrieved');
    }

    public function show(User $user): JsonResponse
    {
        return $this->success([
            'user' => new UserResource($user),
            'available_roles' => Role::where('guard_name', 'api')->pluck('name'),
        ], 'User retrieved');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        $user->load('roles');

        return $this->success(['user' => new UserResource($user)], 'User updated');
    }

    public function updateRoles(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in(Role::where('guard_name', 'api')->pluck('name'))],
        ]);

        $user->syncRoles($validated['roles']);
        $user->load('roles');

        return $this->success(['user' => new UserResource($user)], 'Roles updated');
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->hasRole('super-admin') && User::role('super-admin')->count() <= 1) {
            return $this->error(null, 'Cannot delete the last super-admin', 422);
        }

        $user->delete();

        return $this->success([], 'User deleted');
    }

    public function tiers(): JsonResponse
    {
        $roles = Role::where('guard_name', 'api')
            ->where('name', '!=', 'super-admin')
            ->with('permissions')
            ->get()
            ->map(function ($role) {
                return [
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name'),
                ];
            });

        return $this->success(['tiers' => $roles], 'Tiers retrieved');
    }

    public function upgrade(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'tier' => ['required', 'string', Rule::in(Role::where('guard_name', 'api')->where('name', '!=', 'super-admin')->pluck('name'))],
        ]);

        if ($user->hasRole('super-admin')) {
            return $this->error(null, 'Cannot change tier of super-admin', 422);
        }

        $tierRole = $validated['tier'];
        $user->syncRoles([$tierRole]);
        $user->load('roles');

        return $this->success([
            'user' => new UserResource($user),
            'confirmed' => true,
            'message' => "User upgraded to {$tierRole} tier",
        ], 'Tier upgraded');
    }
}
