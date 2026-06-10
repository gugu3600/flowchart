<?php

namespace App\Repositories\flow;

interface FlowRepositoryInterface
{
    public function allForUser(int $userId);
    public function find(int $id);
    public function findForUser(int $id, int $userId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function countForUser(int $userId): int;
}
