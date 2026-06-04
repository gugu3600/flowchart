<?php

namespace App\Repositories\flow_node;

use App\Models\FlowNode;

class FlowNodeRepository implements FlowNodeRepositoryInterface
{
    public function deleteByFlowId(int $flowId): void
    {
        FlowNode::where('flow_id', $flowId)->delete();
    }

    public function bulkCreate(int $flowId, array $nodes)
    {
        $instances = collect($nodes)->map(fn ($n) => new FlowNode([
            'flow_id' => $flowId,
            'type' => $n['type'],
            'label' => $n['label'],
            'position_x' => $n['position_x'],
            'position_y' => $n['position_y'],
            'data' => $n['data'] ?? null,
            'config' => $n['config'] ?? null,
        ]));

        return FlowNode::insert($instances->toArray());
    }
}
