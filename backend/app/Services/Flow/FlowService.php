<?php

namespace App\Services\Flow;

use App\Http\Resources\FlowResource;
use App\Repositories\flow\FlowRepositoryInterface;
use App\Repositories\flow_edge\FlowEdgeRepositoryInterface;
use App\Repositories\flow_node\FlowNodeRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class FlowService
{
    private const SILVER_MAX_FLOWS = 5;

    public function __construct(
        private readonly FlowRepositoryInterface $flowRepository,
        private readonly FlowNodeRepositoryInterface $nodeRepository,
        private readonly FlowEdgeRepositoryInterface $edgeRepository,
    ) {}

    public function allForUser(int $userId)
    {
        return $this->flowRepository->allForUser($userId);
    }

    public function findForUser(int $id, int $userId)
    {
        return $this->flowRepository->findForUser($id, $userId);
    }

    public function create(int $userId, array $data)
    {
        $this->checkFlowLimit($userId);
        return $this->flowRepository->create([
            'user_id' => $userId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'config' => $data['config'] ?? null,
        ]);
    }

    public function update(int $id, int $userId, array $data)
    {
        $this->flowRepository->findForUser($id, $userId);
        return $this->flowRepository->update($id, $data);
    }

    public function delete(int $id, int $userId): void
    {
        $this->flowRepository->findForUser($id, $userId);
        $this->flowRepository->delete($id);
    }

    public function saveNodes(int $flowId, int $userId, array $nodes)
    {
        $this->flowRepository->findForUser($flowId, $userId);
        $this->nodeRepository->deleteByFlowId($flowId);
        $this->nodeRepository->bulkCreate($flowId, $nodes);
        return $this->flowRepository->findForUser($flowId, $userId)->nodes;
    }

    public function saveEdges(int $flowId, int $userId, array $edges)
    {
        $this->flowRepository->findForUser($flowId, $userId);
        $this->edgeRepository->deleteByFlowId($flowId);
        $this->edgeRepository->bulkCreate($flowId, $edges);
        return $this->flowRepository->findForUser($flowId, $userId)->edges;
    }

    public function save(int $flowId, int $userId, array $data): array
    {
        $this->flowRepository->findForUser($flowId, $userId);

        $this->nodeRepository->deleteByFlowId($flowId);
        $nodeIdMap = $this->nodeRepository->bulkCreateWithReturn($flowId, $data['nodes'] ?? []);

        $this->edgeRepository->deleteByFlowId($flowId);
        if (!empty($data['edges'])) {
            $mappedEdges = collect($data['edges'])->map(function ($e) use ($nodeIdMap) {
                return [
                    'source_node_id' => $nodeIdMap[$e['source']] ?? 0,
                    'target_node_id' => $nodeIdMap[$e['target']] ?? 0,
                    'label' => $e['label'] ?? null,
                    'config' => $e['config'] ?? null,
                ];
            })->toArray();
            $this->edgeRepository->bulkCreate($flowId, $mappedEdges);
        }

        $flow = $this->flowRepository->findForUser($flowId, $userId);

        return [
            'flow' => new FlowResource($flow),
            'node_id_map' => $nodeIdMap,
        ];
    }

    public function flowCount(int $userId): int
    {
        return $this->flowRepository->countForUser($userId);
    }

    public function maxSlots(int $userId): int
    {
        $user = Auth::user();
        if (!$user) return 0;
        if ($user->hasAnyRole(['platinum', 'gold', 'super-admin'])) return 999;
        if ($user->hasRole('silver')) return self::SILVER_MAX_FLOWS;
        return 0;
    }

    private function checkFlowLimit(int $userId): void
    {
        $user = Auth::user();
        if (!$user) return;
        if ($user->hasAnyRole(['platinum', 'gold', 'super-admin'])) return;
        if ($user->hasRole('silver')) {
            $count = $this->flowRepository->countForUser($userId);
            if ($count >= self::SILVER_MAX_FLOWS) {
                abort(403, 'You have reached the maximum of ' . self::SILVER_MAX_FLOWS . ' flows. Upgrade to Gold or higher for unlimited flows.');
            }
            return;
        }
        abort(403, 'Saving requires a Silver or higher subscription.');
    }
}
