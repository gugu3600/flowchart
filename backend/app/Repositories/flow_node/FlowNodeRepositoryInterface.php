<?php

namespace App\Repositories\flow_node;

interface FlowNodeRepositoryInterface
{
    public function deleteByFlowId(int $flowId);
    public function bulkCreate(int $flowId, array $nodes);
}
