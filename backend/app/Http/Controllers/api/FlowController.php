<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Flow\SaveEdgesRequest;
use App\Http\Requests\Flow\SaveNodesRequest;
use App\Http\Requests\Flow\StoreFlowRequest;
use App\Http\Requests\Flow\UpdateFlowRequest;
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
        $flows = $this->flowService->allForUser(Auth::guard('api')->id());

        return $this->success(['flows' => $flows], 'Flows retrieved');
    }

    public function store(StoreFlowRequest $request): JsonResponse
    {
        $flow = $this->flowService->create(
            Auth::guard('api')->id(),
            $request->validated(),
        );

        return $this->success(['flow' => $flow], 'Flow created', 201);
    }

    public function show(Flow $flow): JsonResponse
    {
        $flow = $this->flowService->findForUser($flow->id, Auth::guard('api')->id());

        return $this->success(['flow' => $flow], 'Flow retrieved');
    }

    public function update(UpdateFlowRequest $request, Flow $flow): JsonResponse
    {
        $flow = $this->flowService->update(
            $flow->id,
            Auth::guard('api')->id(),
            $request->validated(),
        );

        return $this->success(['flow' => $flow], 'Flow updated');
    }

    public function destroy(Flow $flow): JsonResponse
    {
        $this->flowService->delete($flow->id, Auth::guard('api')->id());

        return $this->success([], 'Flow deleted');
    }

    public function saveNodes(SaveNodesRequest $request, Flow $flow): JsonResponse
    {
        $nodes = $this->flowService->saveNodes(
            $flow->id,
            Auth::guard('api')->id(),
            $request->validated('nodes'),
        );

        return $this->success(['nodes' => $nodes], 'Nodes saved');
    }

    public function saveEdges(SaveEdgesRequest $request, Flow $flow): JsonResponse
    {
        $edges = $this->flowService->saveEdges(
            $flow->id,
            Auth::guard('api')->id(),
            $request->validated('edges'),
        );

        return $this->success(['edges' => $edges], 'Edges saved');
    }
}
