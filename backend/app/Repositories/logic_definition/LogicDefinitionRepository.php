<?php

namespace App\Repositories\logic_definition;

use App\Models\LogicDefinition;

class LogicDefinitionRepository implements LogicDefinitionRepositoryInterface
{
    public function allForUser(int $userId)
    {
        return LogicDefinition::where('user_id', $userId)->get();
    }

    public function findForUser(int $id, int $userId)
    {
        return LogicDefinition::where('user_id', $userId)->findOrFail($id);
    }

    public function create(array $data)
    {
        return LogicDefinition::create($data);
    }

    public function update(int $id, array $data)
    {
        $record = LogicDefinition::findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id)
    {
        return LogicDefinition::destroy($id);
    }

    public function countForUser(int $userId): int
    {
        return LogicDefinition::where('user_id', $userId)->count();
    }
}
