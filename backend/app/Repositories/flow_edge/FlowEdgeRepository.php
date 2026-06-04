<?php

namespace App\Repositories\flow_edge;

use App\Models\FlowEdge;

class FlowEdgeRepository implements FlowEdgeRepositoryInterface
{
    public function deleteByFlowId(int $flowId): void
    {
        FlowEdge::where('flow_id', $flowId)->delete();
    }

    public function bulkCreate(int $flowId, array $edges)
    {
        $instances = collect($edges)->map(fn ($e) => new FlowEdge([
            'flow_id' => $flowId,
            'source_node_id' => $e['source_node_id'],
            'target_node_id' => $e['target_node_id'],
            'label' => $e['label'] ?? null,
            'config' => $e['config'] ?? null,
        ]));

        return FlowEdge::insert($instances->toArray());
    }
}
