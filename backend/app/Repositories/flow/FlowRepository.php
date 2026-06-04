<?php

namespace App\Repositories\flow;

use App\Models\Flow;

class FlowRepository implements FlowRepositoryInterface
{
    public function allForUser(int $userId)
    {
        return Flow::where('user_id', $userId)->get();
    }

    public function find(int $id)
    {
        return Flow::with(['nodes', 'edges'])->findOrFail($id);
    }

    public function findForUser(int $id, int $userId)
    {
        return Flow::where('user_id', $userId)->with(['nodes', 'edges'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Flow::create($data);
    }

    public function update(int $id, array $data)
    {
        $flow = Flow::findOrFail($id);
        $flow->update($data);
        return $flow;
    }

    public function delete(int $id)
    {
        return Flow::destroy($id);
    }
}
