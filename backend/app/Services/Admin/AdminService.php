<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\flow\FlowRepositoryInterface;
use App\Repositories\logic_definition\LogicDefinitionRepositoryInterface;
use App\Repositories\table_definition\TableDefinitionRepositoryInterface;
use App\Repositories\user\UserRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class AdminService
{
    private const SUBSCRIPTION_DURATIONS = [
        'silver' => 33,
        'gold' => 37,
        'platinum' => 44,
    ];

    public function __construct(
        private readonly FlowRepositoryInterface $flowRepository,
        private readonly LogicDefinitionRepositoryInterface $logicRepository,
        private readonly TableDefinitionRepositoryInterface $tableRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function getStats(): array
    {
        return [
            ['label' => 'Users', 'value' => (string) User::count(), 'icon' => '&#128101;', 'color' => '#3b82f6'],
            ['label' => 'Flows', 'value' => (string) $this->flowRepository->all()->count(), 'icon' => '&#128196;', 'color' => '#8b5cf6'],
            ['label' => 'Tables', 'value' => (string) $this->tableRepository->all()->count(), 'icon' => '&#128202;', 'color' => '#10b981'],
            ['label' => 'Logics', 'value' => (string) $this->logicRepository->all()->count(), 'icon' => '&#9881;', 'color' => '#f59e0b'],
        ];
    }

    public function getAllLogics(): array
    {
        return $this->logicRepository->all()->map(function ($logic) {
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
        })->toArray();
    }

    public function getAllTables(): array
    {
        return $this->tableRepository->all()->map(function ($table) {
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
        })->toArray();
    }

    public function getAllFlows(): array
    {
        return $this->flowRepository->all()->map(function ($flow) {
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
        })->toArray();
    }

    public function getUsers(): array
    {
        $users = User::with('roles')->orderBy('created_at', 'desc')->paginate(20);

        return [
            'users' => $users,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ];
    }

    public function getUser(int $id): array
    {
        $user = $this->userRepository->find($id);
        $user->load('roles');

        return [
            'user' => $user,
            'available_roles' => Role::where('guard_name', 'api')->pluck('name'),
        ];
    }

    public function updateUser(int $id, array $data): User
    {
        $user = $this->userRepository->find($id);
        $user->update($data);
        $user->load('roles');
        return $user;
    }

    public function updateUserRoles(int $id, array $roles): User
    {
        $user = $this->userRepository->find($id);
        $user->syncRoles($roles);
        $user->load('roles');
        return $user;
    }

    public function deleteUser(int $id): void
    {
        $user = $this->userRepository->find($id);

        if ($user->hasRole('super-admin') && User::role('super-admin')->count() <= 1) {
            abort(422, 'Cannot delete the last super-admin');
        }

        $user->delete();
    }

    public function getTierRoleNames()
    {
        return Role::where('guard_name', 'api')->pluck('name');
    }

    public function getTiers(): array
    {
        return Role::where('guard_name', 'api')
            ->where('name', '!=', 'super-admin')
            ->with('permissions')
            ->get()
            ->map(function ($role) {
                return [
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name'),
                ];
            })->toArray();
    }

    public function upgradeUser(int $id, string $tier): User
    {
        $user = $this->userRepository->find($id);

        if ($user->hasRole('super-admin')) {
            abort(422, 'Cannot change tier of super-admin');
        }

        $user->syncRoles([$tier]);

        if (isset(self::SUBSCRIPTION_DURATIONS[$tier])) {
            $days = self::SUBSCRIPTION_DURATIONS[$tier];
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
        return $user;
    }
}
