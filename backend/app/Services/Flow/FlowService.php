<?php

namespace App\Services\Flow;

use App\Http\Resources\FlowResource;
use App\Models\User;
use App\Repositories\flow\FlowRepositoryInterface;
use App\Repositories\flow_edge\FlowEdgeRepositoryInterface;
use App\Repositories\flow_node\FlowNodeRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($userId, $data) {
            $this->checkFlowLimit($userId);
            return $this->flowRepository->create([
                'user_id' => $userId,
                'name' => strip_tags($data['name']),
                'description' => isset($data['description']) ? strip_tags($data['description']) : null,
                'config' => $data['config'] ?? null,
            ]);
        });
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

        $payloadTypes = collect($nodes)->pluck('type')->unique()->toArray();

        if (!empty($payloadTypes)) {
            $this->nodeRepository->deleteByFlowIdAndTypes($flowId, $payloadTypes);
        }

        $sanitized = array_map(fn ($n) => [
            ...$n,
            'label' => isset($n['label']) ? strip_tags($n['label']) : '',
        ], $nodes);
        $this->nodeRepository->bulkCreate($flowId, $sanitized);
        return $this->flowRepository->findForUser($flowId, $userId)->nodes;
    }

    public function saveEdges(int $flowId, int $userId, array $edges)
    {
        $this->flowRepository->findForUser($flowId, $userId);

        $edgeNodeIds = collect($edges)->flatMap(fn ($e) => [
            $e['source_node_id'] ?? null,
            $e['target_node_id'] ?? null,
        ])->filter()->unique()->toArray();

        if (!empty($edgeNodeIds)) {
            $this->edgeRepository->deleteByNodeIds($flowId, $edgeNodeIds);
        }

        $sanitized = array_map(fn ($e) => [
            ...$e,
            'label' => isset($e['label']) ? strip_tags($e['label']) : null,
        ], $edges);
        $this->edgeRepository->bulkCreate($flowId, $sanitized);
        return $this->flowRepository->findForUser($flowId, $userId)->edges;
    }

    public function save(int $flowId, int $userId, array $data): array
    {
        $this->flowRepository->findForUser($flowId, $userId);

        // Only touch nodes of the types in the payload (preserves cross-type nodes)
        $payloadTypes = collect($data['nodes'] ?? [])->pluck('type')->unique()->toArray();

        $deletedNodeIds = [];
        if (!empty($payloadTypes)) {
            $deletedNodeIds = $this->nodeRepository->deleteByFlowIdAndTypes($flowId, $payloadTypes);
        }

        $this->edgeRepository->deleteByNodeIds($flowId, $deletedNodeIds);

        $sanitizedNodes = array_map(fn ($n) => [
            ...$n,
            'label' => isset($n['label']) ? strip_tags($n['label']) : '',
        ], $data['nodes'] ?? []);

        $nodeIdMap = $this->nodeRepository->bulkCreateWithReturn($flowId, $sanitizedNodes);

        if (!empty($data['edges'])) {
            $mappedEdges = collect($data['edges'])->filter(function ($e) use ($nodeIdMap) {
                return isset($nodeIdMap[$e['source']]) && isset($nodeIdMap[$e['target']]);
            })->map(function ($e) use ($nodeIdMap) {
                return [
                    'source_node_id' => $nodeIdMap[$e['source']],
                    'target_node_id' => $nodeIdMap[$e['target']],
                    'label' => isset($e['label']) ? strip_tags($e['label']) : null,
                    'config' => $e['config'] ?? null,
                ];
            })->values()->toArray();
            $this->edgeRepository->bulkCreate($flowId, $mappedEdges);
        }

        $flow = $this->flowRepository->findForUser($flowId, $userId);

        return [
            'flow' => new FlowResource($flow),
            'node_id_map' => $nodeIdMap,
        ];
    }

    public function validateConnection(int $flowId, int $userId, string $sourceId, string $targetId, string $sourceType, string $targetType): array
    {
        $user = Auth::user();

        if (!$user) {
            return ['valid' => false, 'message' => 'Authentication required.'];
        }

        if (!$user->hasPermissionTo('save-flows')) {
            return ['valid' => false, 'message' => 'Saving requires a Silver or higher subscription. Upgrade to save your work.'];
        }

        if ($sourceId === $targetId) {
            return ['valid' => false, 'message' => 'Cannot connect a node to itself.'];
        }

        $isSchema = $sourceType === 'table' || $targetType === 'table';
        if ($isSchema) {
            if ($sourceType !== 'table' || $targetType !== 'table') {
                return ['valid' => false, 'message' => 'In schema mode, only table nodes can be connected.'];
            }
        } else {
            if ($sourceType === 'table' || $targetType === 'table') {
                return ['valid' => false, 'message' => 'In flow mode, table nodes cannot be connected.'];
            }
        }

        $this->flowRepository->findForUser($flowId, $userId);

        // if both nodes have DB IDs (already saved), check for duplicate edge
        if (is_numeric($sourceId) && is_numeric($targetId)) {
            $exists = $this->edgeRepository->exists($flowId, (int)$sourceId, (int)$targetId);
            if ($exists) {
                return ['valid' => false, 'message' => 'These nodes are already connected.'];
            }
        }

        return ['valid' => true, 'message' => ''];
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
            // Lock user row to serialize concurrent flow creation
            User::where('id', $userId)->lockForUpdate()->first();
            $count = $this->flowRepository->countForUser($userId);
            if ($count >= self::SILVER_MAX_FLOWS) {
                abort(403, 'You have reached the maximum of ' . self::SILVER_MAX_FLOWS . ' flows. Upgrade to Gold or higher for unlimited flows.');
            }
            return;
        }
        abort(403, 'Saving requires a Silver or higher subscription.');
    }
}
