<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Admin\UpdateRolesRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpgradeUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Admin\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AdminController extends BaseController
{
    public function __construct(
        private readonly AdminService $adminService,
    ) {}

    public function stats(): JsonResponse
    {
        return $this->success([
            'stats' => $this->adminService->getStats(),
        ], 'Stats retrieved');
    }

    public function logics(): JsonResponse
    {
        return $this->success([
            'logics' => $this->adminService->getAllLogics(),
        ], 'Logics retrieved');
    }

    public function tables(): JsonResponse
    {
        return $this->success([
            'tables' => $this->adminService->getAllTables(),
        ], 'Tables retrieved');
    }

    public function flows(): JsonResponse
    {
        return $this->success([
            'flows' => $this->adminService->getAllFlows(),
        ], 'Flows retrieved');
    }

    public function users(): JsonResponse
    {
        $result = $this->adminService->getUsers();

        return $this->success([
            'users' => UserResource::collection($result['users']),
            'meta' => $result['meta'],
        ], 'Users retrieved');
    }

    public function show(User $user): JsonResponse
    {
        $user->load('roles');

        return $this->success([
            'user' => new UserResource($user),
            'available_roles' => $this->adminService->getTierRoleNames(),
        ], 'User retrieved');
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $updated = $this->adminService->updateUser($user->id, $data);

        return $this->success(['user' => new UserResource($updated)], 'User updated');
    }

    public function updateRoles(UpdateRolesRequest $request, User $user): JsonResponse
    {
        $updated = $this->adminService->updateUserRoles($user->id, $request->validated()['roles']);

        return $this->success(['user' => new UserResource($updated)], 'Roles updated');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->adminService->deleteUser($user->id);

        return $this->success([], 'User deleted');
    }

    public function tiers(): JsonResponse
    {
        return $this->success([
            'tiers' => $this->adminService->getTiers(),
        ], 'Tiers retrieved');
    }

    public function upgrade(UpgradeUserRequest $request, User $user): JsonResponse
    {
        $tier = $request->validated()['tier'];
        $updated = $this->adminService->upgradeUser($user->id, $tier);

        return $this->success([
            'user' => new UserResource($updated),
            'confirmed' => true,
            'message' => "User upgraded to {$tier} tier",
        ], 'Tier upgraded');
    }
}
