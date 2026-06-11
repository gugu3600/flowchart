<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Flow\SaveEdgesRequest;
use App\Http\Requests\Flow\SaveFlowRequest;
use App\Http\Requests\Flow\SaveNodesRequest;
use App\Http\Requests\Flow\StoreFlowRequest;
use App\Http\Requests\Flow\UpdateFlowRequest;
use App\Http\Requests\Flow\ValidateConnectionRequest;
use App\Http\Resources\FlowEdgeResource;
use App\Http\Resources\FlowNodeResource;
use App\Http\Resources\FlowResource;
use App\Models\Flow;
use App\Services\Flow\FlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FlowController extends BaseController
{
    public function __construct(
        private readonly FlowService $flowService,
    ) {}

    public function index(): JsonResponse
    {
        $flows = $this->flowService->allForUser(Auth::id());
        $count = $this->flowService->flowCount(Auth::id());
        $max = $this->flowService->maxSlots(Auth::id());

        return $this->success([
            'flows' => FlowResource::collection($flows),
            'flow_count' => $count,
            'max_slots' => $max,
        ], 'Flows retrieved');
    }

    public function store(StoreFlowRequest $request): JsonResponse
    {
        $flow = $this->flowService->create(Auth::id(), $request->validated());

        return $this->success(
            ['flow' => new FlowResource($flow)],
            'Flow created',
            201,
        );
    }

    public function show(Flow $flow): JsonResponse
    {
        $flow = $this->flowService->findForUser($flow->id, Auth::id());

        return $this->success(
            ['flow' => new FlowResource($flow)],
            'Flow retrieved',
        );
    }

    public function update(UpdateFlowRequest $request, Flow $flow): JsonResponse
    {
        $flow = $this->flowService->update($flow->id, Auth::id(), $request->validated());

        return $this->success(
            ['flow' => new FlowResource($flow)],
            'Flow updated',
        );
    }

    public function destroy(Flow $flow): JsonResponse
    {
        $this->flowService->delete($flow->id, Auth::id());

        return $this->success([], 'Flow deleted');
    }

    public function saveNodes(SaveNodesRequest $request, Flow $flow): JsonResponse
    {
        $nodes = $this->flowService->saveNodes($flow->id, Auth::id(), $request->validated('nodes'));

        return $this->success(
            ['nodes' => FlowNodeResource::collection($nodes)],
            'Nodes saved',
        );
    }

    public function saveEdges(SaveEdgesRequest $request, Flow $flow): JsonResponse
    {
        $edges = $this->flowService->saveEdges($flow->id, Auth::id(), $request->validated('edges'));

        return $this->success(
            ['edges' => FlowEdgeResource::collection($edges)],
            'Edges saved',
        );
    }

    public function save(SaveFlowRequest $request, Flow $flow): JsonResponse
    {
        $result = $this->flowService->save($flow->id, Auth::id(), $request->validated());

        return $this->success($result, 'Flow saved');
    }

    public function validateConnection(ValidateConnectionRequest $request, Flow $flow): JsonResponse
    {
        $result = $this->flowService->validateConnection(
            $flow->id,
            Auth::id(),
            $request->validated('source_id'),
            $request->validated('target_id'),
            $request->validated('source_type'),
            $request->validated('target_type'),
        );

        if ($result['valid']) {
            return $this->success([], 'Connection is valid');
        }

        return $this->error([], $result['message'], 422);
    }
}
