<?php

namespace App\Services\TableDefinition;

use App\Repositories\table_definition\TableDefinitionRepositoryInterface;

class TableDefinitionService
{
    public function __construct(
        private readonly TableDefinitionRepositoryInterface $repo,
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
            'columns' => $data['columns'],
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
