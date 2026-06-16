<?php

namespace App\Repositories\flow_node;

interface FlowNodeRepositoryInterface
{
    public function deleteByFlowId(int $flowId);
    public function deleteByFlowIdAndTypes(int $flowId, array $types): array;
    public function bulkCreate(int $flowId, array $nodes);
    public function bulkCreateWithReturn(int $flowId, array $nodes): array;
}
