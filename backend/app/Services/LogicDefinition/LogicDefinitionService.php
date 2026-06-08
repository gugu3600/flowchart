<?php

namespace App\Services\LogicDefinition;

use App\Repositories\logic_definition\LogicDefinitionRepositoryInterface;

class LogicDefinitionService
{
    public function __construct(
        private readonly LogicDefinitionRepositoryInterface $repo,
    ) {}

    public function allForUser(int $userId)
    {
        return $this->repo->allForUser($userId);
    }

    public function findForUser(int $id, int $userId)
    {
        return $this->repo->findForUser($id, $userId);
    }

    public function create(int $userId, array $data)
    {
        return $this->repo->create([
            'user_id' => $userId,
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
}
