<?php

namespace App\Repositories\logic_definition;

use App\Models\LogicDefinition;

class LogicDefinitionRepository implements LogicDefinitionRepositoryInterface
{
    public function all()
    {
        return LogicDefinition::with(['user:id,name,email'])->orderBy('created_at', 'desc')->get();
    }

    public function allForUser(int $userId)
    {
        return LogicDefinition::where('user_id', $userId)->get();
    }

    public function allForUserAndFlow(int $userId, int $flowId)
    {
        return LogicDefinition::where('user_id', $userId)->where('flow_id', $flowId)->get();
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

    public function countForUserAndFlow(int $userId, int $flowId): int
    {
        return LogicDefinition::where('user_id', $userId)->where('flow_id', $flowId)->count();
    }
}
