<?php

namespace App\Http\Controllers\api;

use App\Http\Resources\UserResource;
use App\Models\Flow;
use App\Models\LogicDefinition;
use App\Models\TableDefinition;
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

    public function logics(): JsonResponse
    {
        $logics = LogicDefinition::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($logic) {
                return [
                    'id' => $logic->id,
                    'name' => $logic->name,
                    'description' => $logic->description,
                    'inputs' => $logic->inputs,
                    'output' => $logic->output,
                    'user_id' => $logic->user_id,
                    'owner_name' => $logic->user?->name,
                    'owner_email' => $logic->user?->email,
                    'created_at' => $logic->created_at,
                    'updated_at' => $logic->updated_at,
                ];
            });

        return $this->success(['logics' => $logics], 'Logics retrieved');
    }

    public function tables(): JsonResponse
    {
        $tables = TableDefinition::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'columns' => $table->columns,
                    'user_id' => $table->user_id,
                    'owner_name' => $table->user?->name,
                    'owner_email' => $table->user?->email,
                    'created_at' => $table->created_at,
                    'updated_at' => $table->updated_at,
                ];
            });

        return $this->success(['tables' => $tables], 'Tables retrieved');
    }

    public function flows(): JsonResponse
    {
        $flows = Flow::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($flow) {
                return [
                    'id' => $flow->id,
                    'name' => $flow->name,
                    'description' => $flow->description,
                    'config' => $flow->config,
                    'user_id' => $flow->user_id,
                    'owner_name' => $flow->user?->name,
                    'owner_email' => $flow->user?->email,
                    'created_at' => $flow->created_at,
                    'updated_at' => $flow->updated_at,
                ];
            });

        return $this->success(['flows' => $flows], 'Flows retrieved');
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

    private const SUBSCRIPTION_DURATIONS = [
        'silver' => 33,
        'gold' => 37,
        'platinum' => 44,
    ];

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

        if (isset(self::SUBSCRIPTION_DURATIONS[$tierRole])) {
            $days = self::SUBSCRIPTION_DURATIONS[$tierRole];
            $now = now();
            $currentExpiry = $user->subscription_expires_at;
            $base = ($currentExpiry && $currentExpiry->isFuture()) ? $currentExpiry : $now;
            $user->subscription_expires_at = $base->copy()->addDays($days);
            $user->save();
        } else {
            $user->subscription_expires_at = null;
            $user->save();
        }

        $user->load('roles');

        return $this->success([
            'user' => new UserResource($user),
            'confirmed' => true,
            'message' => "User upgraded to {$tierRole} tier",
        ], 'Tier upgraded');
    }
}
