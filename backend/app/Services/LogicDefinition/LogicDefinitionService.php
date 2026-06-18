<?php

namespace App\Services\LogicDefinition;

use App\Repositories\logic_definition\LogicDefinitionRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class LogicDefinitionService
{
    private const FREE_MAX_LOGICS = 4;

    public function __construct(
        private readonly LogicDefinitionRepositoryInterface $repo,
    ) {}

    public function allForUser(int $userId)
    {
        return $this->repo->allForUser($userId);
    }

    public function allForUserAndFlow(int $userId, int $flowId)
    {
        return $this->repo->allForUserAndFlow($userId, $flowId);
    }

    public function findForUser(int $id, int $userId)
    {
        return $this->repo->findForUser($id, $userId);
    }

    public function create(int $userId, array $data)
    {
        $this->checkLogicLimit($userId, isset($data['flow_id']) ? (int) $data['flow_id'] : null);
        return $this->repo->create([
            'user_id' => $userId,
            'flow_id' => $data['flow_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'inputs' => $data['inputs'] ?? [],
            'output' => $data['output'] ?? null,
        ]);
    }

    public function update(int $id, int $userId, array $data)
    {
        $this->repo->findForUser($id, $userId);
        return $this->repo->update($id, $data);
    }

    public function delete(int $id, int $userId): void
    {
        $this->repo->findForUser($id, $userId);
        $this->repo->delete($id);
    }

    public function logicCount(int $userId): int
    {
        return $this->repo->countForUser($userId);
    }

    public function maxLogicSlots(int $userId): int
    {
        $user = Auth::user();
        if (!$user) return 0;
        if ($user->hasAnyRole(['silver', 'gold', 'platinum', 'super-admin'])) return 999;
        return self::FREE_MAX_LOGICS;
    }

    private function checkLogicLimit(int $userId, ?int $flowId = null): void
    {
        $user = Auth::user();
        if (!$user) return;
        if ($user->hasAnyRole(['silver', 'gold', 'platinum', 'super-admin'])) return;
        $count = $flowId
            ? $this->repo->countForUserAndFlow($userId, $flowId)
            : $this->repo->countForUser($userId);
        if ($count >= self::FREE_MAX_LOGICS) {
            abort(403, 'You have reached the maximum of ' . self::FREE_MAX_LOGICS . ' logics. Upgrade to Silver or higher for unlimited logics.');
        }
    }
}
