<?php

namespace App\Repositories\table_definition;

use App\Models\TableDefinition;

class TableDefinitionRepository implements TableDefinitionRepositoryInterface
{
    public function all()
    {
        return TableDefinition::with(['user:id,name,email'])->orderBy('created_at', 'desc')->get();
    }

    public function allForUser(int $userId)
    {
        return TableDefinition::where('user_id', $userId)->get();
    }

    public function findForUser(int $id, int $userId)
    {
        return TableDefinition::where('user_id', $userId)->findOrFail($id);
    }

    public function create(array $data)
    {
        return TableDefinition::create($data);
    }

    public function update(int $id, array $data)
    {
        $record = TableDefinition::findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id)
    {
        return TableDefinition::destroy($id);
    }
}
