<?php

namespace App\Http\Controllers\api;

use App\Models\Flow;
use App\Models\FlowEdge;
use App\Models\FlowNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FlowController extends BaseController
{
    public function index(): JsonResponse
    {
        $flows = Flow::where('user_id', Auth::guard('api')->id())->get();

        return $this->success(['flows' => $flows], 'Flows retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $flow = Flow::create([
            'user_id' => Auth::guard('api')->id(),
            'name' => $request->name,
            'description' => $request->description,
            'config' => $request->config,
        ]);

        return $this->success(['flow' => $flow], 'Flow created', 201);
    }

    public function show(Flow $flow): JsonResponse
    {
        if ($flow->user_id !== Auth::guard('api')->id()) {
            return $this->error(null, 'Forbidden', 403);
        }

        $flow->load(['nodes', 'edges']);

        return $this->success(['flow' => $flow], 'Flow retrieved');
    }

    public function update(Request $request, Flow $flow): JsonResponse
    {
        if ($flow->user_id !== Auth::guard('api')->id()) {
            return $this->error(null, 'Forbidden', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $flow->update($request->only('name', 'description', 'config'));

        return $this->success(['flow' => $flow], 'Flow updated');
    }

    public function destroy(Flow $flow): JsonResponse
    {
        if ($flow->user_id !== Auth::guard('api')->id()) {
            return $this->error(null, 'Forbidden', 403);
        }

        $flow->delete();

        return $this->success([], 'Flow deleted');
    }

    public function saveNodes(Request $request, Flow $flow): JsonResponse
    {
        if ($flow->user_id !== Auth::guard('api')->id()) {
            return $this->error(null, 'Forbidden', 403);
        }

        $validator = Validator::make($request->all(), [
            'nodes' => 'required|array',
            'nodes.*.id' => 'nullable|string',
            'nodes.*.type' => 'required|string',
            'nodes.*.label' => 'required|string',
            'nodes.*.position_x' => 'required|numeric',
            'nodes.*.position_y' => 'required|numeric',
            'nodes.*.data' => 'nullable|array',
            'nodes.*.config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $flow->nodes()->delete();
        $nodes = collect($request->nodes)->map(fn ($n) => new FlowNode([
            'type' => $n['type'],
            'label' => $n['label'],
            'position_x' => $n['position_x'],
            'position_y' => $n['position_y'],
            'data' => $n['data'] ?? null,
            'config' => $n['config'] ?? null,
        ]));
        $flow->nodes()->saveMany($nodes);

        return $this->success(['nodes' => $flow->nodes()->fresh()->get()], 'Nodes saved');
    }

    public function saveEdges(Request $request, Flow $flow): JsonResponse
    {
        if ($flow->user_id !== Auth::guard('api')->id()) {
            return $this->error(null, 'Forbidden', 403);
        }

        $validator = Validator::make($request->all(), [
            'edges' => 'required|array',
            'edges.*.source_node_id' => 'required|integer|exists:flow_nodes,id',
            'edges.*.target_node_id' => 'required|integer|exists:flow_nodes,id',
            'edges.*.label' => 'nullable|string',
            'edges.*.config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $flow->edges()->delete();
        $edges = collect($request->edges)->map(fn ($e) => new FlowEdge([
            'source_node_id' => $e['source_node_id'],
            'target_node_id' => $e['target_node_id'],
            'label' => $e['label'] ?? null,
            'config' => $e['config'] ?? null,
        ]));
        $flow->edges()->saveMany($edges);

        return $this->success(['edges' => $flow->edges()->fresh()->get()], 'Edges saved');
    }
}
