<?php

namespace App\Repositories\flow_edge;

interface FlowEdgeRepositoryInterface
{
    public function deleteByFlowId(int $flowId);
    public function deleteByNodeIds(int $flowId, array $nodeIds);
    public function bulkCreate(int $flowId, array $edges);
}
