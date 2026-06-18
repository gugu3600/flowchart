<?php

namespace App\Repositories\table_definition;

interface TableDefinitionRepositoryInterface
{
    public function all();
    public function allForUser(int $userId);
    public function allForUserAndFlow(int $userId, int $flowId);
    public function findForUser(int $id, int $userId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
