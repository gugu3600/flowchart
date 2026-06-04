<?php

namespace App\Repositories\flow_edge;

interface FlowEdgeRepositoryInterface
{
    public function deleteByFlowId(int $flowId);
    public function bulkCreate(int $flowId, array $edges);
}
